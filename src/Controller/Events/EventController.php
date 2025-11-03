<?php

namespace App\Controller\Events;

use App\Entity\Evento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/events', name: 'app_events_list')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(Evento::class);

        // Obtener filtros del request
        $fecha = $request->query->get('fecha');
        $tipo = $request->query->get('tipo');
       

        $qb = $repo->createQueryBuilder('e');

        if ($fecha) {
            $qb->andWhere('DATE(e.fecha) = :fecha')
            ->setParameter('fecha', $fecha);
        }

        if ($tipo) {
            $qb->andWhere('e.tipo = :tipo')
            ->setParameter('tipo', $tipo);
        }


        $eventos = $qb->orderBy('e.fecha', 'DESC')->getQuery()->getResult();

        $sinResultados = count($eventos) === 0;

        // Rol del usuario
        $roleUser = null;
        if ($this->getUser() && method_exists($this->getUser(), 'getRol')) {
            $rolEntity = $this->getUser()->getRol();
            $roleUser = $rolEntity ? $rolEntity->getRol() : null;
        }

        return $this->render('events/index.html.twig', [
            'eventos' => $eventos,
            'sinResultados' => $sinResultados,
            'role_user' => $roleUser,
            'fecha_filtro' => $fecha,
            'tipo_filtro' => $tipo,
        ]);
    }


#[Route('/events/create', name: 'app_events_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $titulo = trim($request->request->get('titulo'));
        $descripcion = trim($request->request->get('descripcion'));
        $fecha = $request->request->get('fecha');
        $tipo = $request->request->get('tipo');
        $latitud = $request->request->get('latitud');
        $longitud = $request->request->get('longitud');
        $imagen = $request->files->get('imagen'); // 📸

        if (!$titulo || !$fecha || !$latitud || !$longitud || !$tipo) {
            $this->addFlash('error', 'Todos los campos son obligatorios.');
            return $this->redirectToRoute('app_events_list');
        }

        $evento = new Evento();
        $evento->setTitulo($titulo);
        $evento->setDescripcion($descripcion);
        $evento->setFecha(new \DateTime($fecha));
        $evento->setTipo($tipo);
        $evento->setLatitud((float)$latitud);
        $evento->setLongitud((float)$longitud);

        // 📷 Guardar imagen si existe
        if ($imagen) {
            $nombreArchivo = uniqid() . '.' . $imagen->guessExtension();
            try {
                $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/events';
                $imagen->move($uploadsDir, $nombreArchivo);

                $evento->setImagen($nombreArchivo);
            } catch (FileException $e) {
                $this->addFlash('error', 'Error al subir la imagen.');
                return $this->redirectToRoute('app_events_list');
            }
        }

        $em->persist($evento);
        $em->flush();

        $this->addFlash('success', 'Evento registrado correctamente.');
        return $this->redirectToRoute('app_events_list');
    }

#[Route('/events/edit/{id}', name: 'app_events_edit', methods: ['POST'])]
public function edit(Request $request, EntityManagerInterface $em, int $id): Response
{
    $evento = $em->getRepository(Evento::class)->find($id);
    if (!$evento) {
        return new JsonResponse(['error' => 'Evento no encontrado.'], 404);
    }

    $titulo = trim($request->request->get('titulo'));
    $descripcion = trim($request->request->get('descripcion'));
    $fecha = $request->request->get('fecha');
    $tipo = $request->request->get('tipo');
    $latitud = $request->request->get('latitud');
    $longitud = $request->request->get('longitud');
    $imagen = $request->files->get('imagen'); // 📸

    if (!$titulo || !$fecha || !$latitud || !$longitud || !$tipo) {
        $this->addFlash('error', 'Todos los campos son obligatorios.');
        return $this->redirectToRoute('app_events_list');
    }

    $evento->setTitulo($titulo);
    $evento->setDescripcion($descripcion);
    $evento->setFecha(new \DateTime($fecha));
    $evento->setTipo($tipo);
    $evento->setLatitud((float)$latitud);
    $evento->setLongitud((float)$longitud);

    // 📷 Si se sube una nueva imagen, eliminar la anterior y reemplazarla
    if ($imagen) {
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/events';
        $imagenAnterior = $evento->getImagen();

        // 🗑️ Eliminar imagen anterior si existe
        if ($imagenAnterior) {
            $rutaAnterior = $uploadsDir . '/' . $imagenAnterior;
            if (file_exists($rutaAnterior)) {
                unlink($rutaAnterior);
            }
        }

        // 📸 Guardar la nueva imagen
        $nombreArchivo = uniqid() . '.' . $imagen->guessExtension();
        try {
            $imagen->move($uploadsDir, $nombreArchivo);
            $evento->setImagen($nombreArchivo);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al subir la nueva imagen.');
            return $this->redirectToRoute('app_events_list');
        }
    }

    $em->flush();

    $this->addFlash('success', 'Evento actualizado correctamente.');
    return $this->redirectToRoute('app_events_list');
}



    #[Route('/events/delete/{id}', name: 'app_events_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $evento = $em->getRepository(Evento::class)->find($id);

        if ($evento) {
            // 📸 Eliminar imagen asociada si existe
            $nombreImagen = $evento->getImagen();
            if ($nombreImagen) {
                $rutaImagen = $this->getParameter('kernel.project_dir') . '/public/uploads/events/' . $nombreImagen;

                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen); // elimina el archivo físico
                }
            }

            // 🗑️ Eliminar evento de la base de datos
            $em->remove($evento);
            $em->flush();

            $this->addFlash('success', 'Evento e imagen eliminados correctamente.');
        } else {
            $this->addFlash('error', 'Evento no encontrado.');
        }

        return $this->redirectToRoute('app_events_list');
    }
}
