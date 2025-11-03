<?php

namespace App\Controller\History;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\HistoriaCarnaval;

final class HistoriaCarnavalController extends AbstractController
{
    #[Route('/history', name: 'app_history_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(HistoriaCarnaval::class);
        $contenido = $request->query->get('contenido');

        $qb = $repo->createQueryBuilder('e');
        if ($contenido) {
            $qb->andWhere('e.titulo LIKE :contenido OR e.contenido LIKE :contenido OR e.contenido2 LIKE :contenido OR e.contenido3 LIKE :contenido')
               ->setParameter('contenido', '%' . $contenido . '%');
        }

        $historias = $qb->getQuery()->getResult();
        $sinResultados = count($historias) === 0;

        return $this->render('history/history.html.twig', [
            'historias' => $historias,
            'sinResultados' => $sinResultados,
            'contenido' => $contenido,
        ]);
    }

    #[Route('/history/create', name: 'app_history_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $titulo = trim($request->request->get('titulo'));
        $texto = trim($request->request->get('contenido'));
        $texto2 = trim($request->request->get('contenido2'));
        $texto3 = trim($request->request->get('contenido3'));
        $imagen = $request->files->get('imagen');

        if (!$titulo || !$texto || !$imagen) {
            $this->addFlash('error', 'El título, contenido e imagen son obligatorios.');
            return $this->redirectToRoute('app_history_index');
        }

        $historia = new HistoriaCarnaval();
        $historia->setTitulo($titulo);
        $historia->setContenido($texto);
        if ($texto2) $historia->setContenido2($texto2);
        if ($texto3) $historia->setContenido3($texto3);

        // ✅ Guardar imagen
        $resultado = $this->procesarImagen($imagen);
        if (isset($resultado['error'])) {
            $this->addFlash('error', $resultado['error']);
            return $this->redirectToRoute('app_history_index');
        }

        $historia->setImagen($resultado['nombreArchivo']);

        $em->persist($historia);
        $em->flush();

        $this->addFlash('success', 'Contenido registrado correctamente.');
        return $this->redirectToRoute('app_history_index');
    }

    #[Route('/history/edit/{id}', name: 'app_history_edit', methods: ['POST'])]
    public function edit(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $historia = $em->getRepository(HistoriaCarnaval::class)->find($id);
        if (!$historia) {
            return new JsonResponse(['error' => 'Historia no encontrada.'], 404);
        }

        $titulo = trim($request->request->get('titulo'));
        $contenido = trim($request->request->get('contenido'));
        $contenido2 = trim($request->request->get('contenido2'));
        $contenido3 = trim($request->request->get('contenido3'));
        $imagen = $request->files->get('imagen');

        if (!$titulo || !$contenido) {
            $this->addFlash('error', 'El título y contenido son obligatorios.');
            return $this->redirectToRoute('app_history_index');
        }

        $historia->setTitulo($titulo);
        $historia->setContenido($contenido);
        $historia->setContenido2($contenido2);
        $historia->setContenido3($contenido3);

        if ($imagen) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/history';
            $imagenAnterior = $historia->getImagen();

            // 🗑️ Eliminar imagen anterior
            if ($imagenAnterior && file_exists($uploadsDir . '/' . $imagenAnterior)) {
                unlink($uploadsDir . '/' . $imagenAnterior);
            }

            // ✅ Procesar nueva imagen
            $resultado = $this->procesarImagen($imagen);
            if (isset($resultado['error'])) {
                $this->addFlash('error', $resultado['error']);
                return $this->redirectToRoute('app_history_index');
            }

            $historia->setImagen($resultado['nombreArchivo']);
        }

        $em->flush();

        $this->addFlash('success', 'Historia actualizada correctamente.');
        return $this->redirectToRoute('app_history_index');
    }

    #[Route('/history/delete/{id}', name: 'app_history_delete', methods: ['POST'])]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $historia = $em->getRepository(HistoriaCarnaval::class)->find($id);

        if ($historia) {
            $rutaImagen = $this->getParameter('kernel.project_dir') . '/public/uploads/history/' . $historia->getImagen();
            if (file_exists($rutaImagen)) unlink($rutaImagen);

            $em->remove($historia);
            $em->flush();

            $this->addFlash('success', 'Historia e imagen eliminadas correctamente.');
        } else {
            $this->addFlash('error', 'Historia no encontrada.');
        }

        return $this->redirectToRoute('app_history_index');
    }

    // 🔧 Función auxiliar robusta para manejar JPG/PNG/WEBP sin errores
    private function procesarImagen($imagen): array
    {
        if (!$imagen->isValid() || !is_readable($imagen->getPathname())) {
            return ['error' => 'La imagen no se pudo leer o contiene caracteres inválidos.'];
        }

        // Detectar tipo MIME para mayor precisión
        $mimeType = $imagen->getMimeType();
        $extension = match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png', 'image/x-png' => 'png',
            'image/webp' => 'webp',
            default => $imagen->guessExtension() ?: 'jpg',
        };

        // Limpiar nombre y generar nombre único
        $nombreOriginal = pathinfo($imagen->getClientOriginalName(), PATHINFO_FILENAME);
        $nombreLimpio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreOriginal);
        $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/history';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        try {
            $imagen->move($uploadsDir, $nombreArchivo);
        } catch (FileException $e) {
            return ['error' => 'Error al mover la imagen: ' . $e->getMessage()];
        }

        return ['nombreArchivo' => $nombreArchivo];
    }
}
