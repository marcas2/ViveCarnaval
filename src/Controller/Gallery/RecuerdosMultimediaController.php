<?php

namespace App\Controller\Gallery;

use App\Entity\Galeria\RecuerdoMultimedia;
use App\Entity\Galeria\InteraccionRecuerdo;
use App\Entity\Login\Usuarios;
use App\Form\Galeria\RecuerdoMultimediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Google\Client;
use Google\Service\YouTube;

final class RecuerdosMultimediaController extends AbstractController
{
    #[Route('/gallery', name: 'app_gallery_index')]
    public function index(EntityManagerInterface $em): Response
    {
        // Obtener todos los recuerdos
        $recuerdos = $em->getRepository(RecuerdoMultimedia::class)->findAll();

        // Repositorio de interacciones
        $interaccionRepo = $em->getRepository(InteraccionRecuerdo::class);

        $datosRecuerdos = [];
        $usuario = $this->getUser();

        foreach ($recuerdos as $recuerdo) {
            // ✅ Contar likes
            $likes = $interaccionRepo->createQueryBuilder('i')
                ->where('i.recuerdo = :recuerdo')
                ->andWhere('i.tipo = :tipo')
                ->setParameter('recuerdo', $recuerdo)
                ->setParameter('tipo', 'like')
                ->select('COUNT(i.id)')
                ->getQuery()
                ->getSingleScalarResult();

            // ✅ Contar comentarios
            $comentarios = $interaccionRepo->createQueryBuilder('i')
                ->where('i.recuerdo = :recuerdo')
                ->andWhere('i.tipo = :tipo')
                ->setParameter('recuerdo', $recuerdo)
                ->setParameter('tipo', 'comentario')
                ->select('COUNT(i.id)')
                ->getQuery()
                ->getSingleScalarResult();

            // ✅ Contar guardados
            $guardados = $interaccionRepo->createQueryBuilder('i')
                ->where('i.recuerdo = :recuerdo')
                ->andWhere('i.tipo = :tipo')
                ->setParameter('recuerdo', $recuerdo)
                ->setParameter('tipo', 'guardado')
                ->select('COUNT(i.id)')
                ->getQuery()
                ->getSingleScalarResult();

            // ✅ Verificar si el usuario actual dio like
            $yaDioLike = false;
            if ($usuario) {
                $yaDioLike = $interaccionRepo->findOneBy([
                    'recuerdo' => $recuerdo,
                    'usuario' => $usuario,
                    'tipo' => 'like',
                ]) !== null;
            }

            // Agregar los resultados al arreglo
            $datosRecuerdos[] = [
                'recuerdo' => $recuerdo,
                'likes' => (int) $likes,
                'comentarios' => (int) $comentarios,
                'guardados' => (int) $guardados,
                'likedByUser' => $yaDioLike,
            ];
        }

        return $this->render('gallery/index.html.twig', [
            'recuerdos' => $datosRecuerdos,
        ]);
    }


    #[Route('/gallery/new', name: 'app_gallery_new')]
public function new(
    Request $request,
    EntityManagerInterface $em,
    SluggerInterface $slugger
): Response {


    $recuerdo = new RecuerdoMultimedia();

    $form = $this->createForm(RecuerdoMultimediaType::class, $recuerdo);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {
         
        $file = $form->get('multimedia')->getData();

        if ($file) {
            $mime = $file->getMimeType();

            // Asignar nombre único
            $newFilename = uniqid('media_') . '.' . $file->guessExtension();
            
            // Mover el archivo a la carpeta definida (por ejemplo public/uploads/recuerdos)
            $file->move($this->getParameter('recuerdos_directory'), $newFilename);

            // Guardar la ruta en la entidad
            $recuerdo->setMultimedia('uploads/recuerdos/' . $newFilename);

            // Determinar si es imagen o video según el tipo MIME
            if (str_starts_with($mime, 'video/')) {
                $recuerdo->setTipo('video');
            } else {
                $recuerdo->setTipo('foto');
            }
        }

        // Asociar usuario autenticado
        $usuario = $this->getUser();
        if ($usuario) {
            $recuerdo->addUsuario($usuario);
        }
         $recuerdo->setEstado('activo');
        $em->persist($recuerdo);
        $em->flush();

        $this->addFlash('success', '✨ ¡Tu recuerdo fue publicado con éxito!');
        return $this->redirectToRoute('app_gallery_index');
    }

    return $this->render('gallery/new.html.twig', [
        'form' => $form->createView(),
    ]);
}



    #[Route('/gallery/delete/{id}', name: 'app_gallery_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);

        if (!$recuerdo) {
            $this->addFlash('error', '❌ El recuerdo no existe.');
            return $this->redirectToRoute('app_gallery_index');
        }

        // Verificar que el usuario autenticado sea dueño del recuerdo
        $usuario = $this->getUser();
        if (!$usuario || $recuerdo->getUsuario() !== $usuario) {
            $this->addFlash('error', '🚫 No tienes permiso para eliminar este recuerdo.');
            return $this->redirectToRoute('app_gallery_index');
        }

        // Eliminar el archivo si es imagen (no video)
        $rutaArchivo = $recuerdo->getMultimedia();
        if ($recuerdo->getTipo() === 'foto' && $rutaArchivo && !str_contains($rutaArchivo, 'youtube.com')) {
            $rutaFisica = $this->getParameter('kernel.project_dir') . '/public/' . $rutaArchivo;
            if (file_exists($rutaFisica)) {
                unlink($rutaFisica);
            }
        }

        $em->remove($recuerdo);
        $em->flush();

        $this->addFlash('success', '🗑️ El recuerdo fue eliminado correctamente.');
        return $this->redirectToRoute('app_gallery_index');
    }

    #[Route('/gallery/edit/{id}', name: 'app_gallery_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        int $id
    ): Response {
        $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);

        if (!$recuerdo) {
            $this->addFlash('error', '❌ El recuerdo no existe.');
            return $this->redirectToRoute('app_gallery_index');
        }

        // ⚠️ Solo el propietario puede editar
        $usuario = $this->getUser();
        if (!$usuario || $recuerdo->getUsuario() !== $usuario) {

            $this->addFlash('error', '🚫 No tienes permiso para editar este recuerdo.');
            return $this->redirectToRoute('app_gallery_index');
        }

        $form = $this->createForm(RecuerdoMultimediaType::class, $recuerdo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('multimedia')->getData();

                    if ($file) {
            $mime = $file->getMimeType();

            // Asignar nombre único
            $newFilename = uniqid('media_') . '.' . $file->guessExtension();
            
            // Mover el archivo a la carpeta definida (por ejemplo public/uploads/recuerdos)
            $file->move($this->getParameter('recuerdos_directory'), $newFilename);

            // Guardar la ruta en la entidad
            $recuerdo->setMultimedia('uploads/recuerdos/' . $newFilename);

            // Determinar si es imagen o video según el tipo MIME
            if (str_starts_with($mime, 'video/')) {
                $recuerdo->setTipo('video');
            } else {
                $recuerdo->setTipo('foto');
            }
        }

            $em->flush();

            $this->addFlash('success', '🖋️ Tu recuerdo fue actualizado con éxito.');
            return $this->redirectToRoute('app_gallery_index');
        }

        return $this->render('gallery/edit.html.twig', [
            'form' => $form->createView(),
            'recuerdo' => $recuerdo,
        ]);
    }

    #[Route('/gallery/like/{id}', name: 'app_gallery_like', methods: ['POST'])]
    public function toggleLike(EntityManagerInterface $em, int $id): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json(['error' => 'No autenticado'], 403);
        }

        $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);
        if (!$recuerdo) {
            return $this->json(['error' => 'Recuerdo no encontrado'], 404);
        }

        $repo = $em->getRepository(InteraccionRecuerdo::class);
        $likeExistente = $repo->findOneBy([
            'recuerdo' => $recuerdo,
            'usuario' => $usuario,
            'tipo' => 'like',
        ]);

        if ($likeExistente) {
            // ❌ Quitar like
            $em->remove($likeExistente);
            $em->flush();
            return $this->json(['liked' => false]);
        } else {
            // ❤️ Dar like
            $nuevoLike = new InteraccionRecuerdo();
            $nuevoLike->setRecuerdo($recuerdo);
            $nuevoLike->setUsuario($usuario);
            $nuevoLike->setTipo('like');
            $nuevoLike->setFecha(new \DateTime());
            $em->persist($nuevoLike);
            $em->flush();
            return $this->json(['liked' => true]);
        }
    }

    #[Route('/gallery/comment/{id}', name: 'app_gallery_comment', methods: ['POST'])]
    public function addComment(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->json(['error' => 'Debes iniciar sesión para comentar'], 403);
        }

        $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);
        if (!$recuerdo) {
            return $this->json(['error' => 'Recuerdo no encontrado'], 404);
        }

        $contenido = trim($request->request->get('comentario'));
        if (empty($contenido)) {
            return $this->json(['error' => 'El comentario no puede estar vacío'], 400);
        }

        $comentario = new InteraccionRecuerdo();
        $comentario->setRecuerdo($recuerdo);
        $comentario->setUsuario($usuario);
        $comentario->setTipo('comentario');
        $comentario->setContenidoComentario($contenido);
        $comentario->setFecha(new \DateTime());

        $em->persist($comentario);
        $em->flush();

        return $this->json([
            'success' => true,
            'usuario' => $usuario->getNombre(),
            'contenido' => $contenido,
            'fecha' => $comentario->getFecha()->format('Y-m-d H:i')
        ]);
    }

#[Route('/gallery/comments/{id}', name: 'app_gallery_get_comments', methods: ['GET'])]
public function getComments(EntityManagerInterface $em, int $id): Response
{
    $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);
    if (!$recuerdo) {
        return $this->json([]);
    }

    $comentarios = $em->getRepository(InteraccionRecuerdo::class)->findBy(
        ['recuerdo' => $recuerdo, 'tipo' => 'comentario'],
        ['fecha' => 'DESC']
    );

    $usuarioActual = $this->getUser();

    $data = array_map(fn($c) => [
        'id' => $c->getId(),
        'usuario' => $c->getUsuario()->getNombre(),
        'contenido' => $c->getContenidoComentario(),
        'fecha' => $c->getFecha()->format('Y-m-d H:i'),
        'esPropio' => $usuarioActual && $c->getUsuario() === $usuarioActual
    ], $comentarios);

    return $this->json($data);
}


#[Route('/gallery/comment/edit/{id}', name: 'app_gallery_comment_edit', methods: ['POST'])]
public function editComment(Request $request, EntityManagerInterface $em, int $id): Response
{
    $usuario = $this->getUser();
    if (!$usuario) {
        return $this->json(['error' => 'No autenticado'], 403);
    }

    $comentario = $em->getRepository(InteraccionRecuerdo::class)->find($id);
    if (!$comentario || $comentario->getUsuario() !== $usuario) {
        return $this->json(['error' => 'No tienes permiso para editar este comentario'], 403);
    }

    $nuevoContenido = trim($request->request->get('comentario'));
    if (empty($nuevoContenido)) {
        return $this->json(['error' => 'El comentario no puede estar vacío'], 400);
    }

    $comentario->setContenidoComentario($nuevoContenido);
    $comentario->setFecha(new \DateTime());
    $em->flush();

    return $this->json([
        'success' => true,
        'contenido' => $nuevoContenido,
        'fecha' => $comentario->getFecha()->format('Y-m-d H:i')
    ]);
}

#[Route('/gallery/comment/delete/{id}', name: 'app_gallery_comment_delete', methods: ['POST'])]
public function deleteComment(EntityManagerInterface $em, int $id): Response
{
    $usuario = $this->getUser();
    if (!$usuario) {
        return $this->json(['error' => 'No autenticado'], 403);
    }

    $comentario = $em->getRepository(InteraccionRecuerdo::class)->find($id);
    if (!$comentario || $comentario->getUsuario() !== $usuario) {
        return $this->json(['error' => 'No tienes permiso para eliminar este comentario'], 403);
    }

    $em->remove($comentario);
    $em->flush();

    return $this->json(['success' => true]);
}
#[Route('/gallery/view/{id}', name: 'app_gallery_view')]
public function view(EntityManagerInterface $em, int $id): Response
{
    $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);
    if (!$recuerdo) {
        throw $this->createNotFoundException('Recuerdo no encontrado');
    }

    return $this->render('gallery/view.html.twig', [
        'recuerdo' => $recuerdo,
    ]);
}



        
}
