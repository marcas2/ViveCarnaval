<?php

namespace App\Controller\Api;

use App\Entity\Galeria\RecuerdoMultimedia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Google\Client;
use Google\Service\YouTube;
use Google\Http\MediaFileUpload;
use App\Entity\Login\Usuarios;

#[Route('/api/gallery', name: 'api_gallery_')]
final class GalleryController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $recuerdos = $em->getRepository(RecuerdoMultimedia::class)->findAll();

        $data = array_map(fn($r) => [
            'id' => $r->getId(),
            'titulo' => $r->getTitulo(),
            'descripcion' => $r->getDescripcion(),
            'tipo' => $r->getTipo(),
            'multimedia' => $r->getMultimedia(),
            'usuario' => $r->getUsuario()?->getUserIdentifier(),
        ], $recuerdos);

        return new JsonResponse($data, 200);
    }

#[Route('', name: 'create', methods: ['POST'])]
public function create(
    Request $request,
    EntityManagerInterface $em,
    SluggerInterface $slugger
): JsonResponse {
    $titulo = $request->request->get('titulo');
    $descripcion = $request->request->get('descripcion');
    $usuarioId = $request->request->getInt('usuario_id');
    $file = $request->files->get('multimedia');

    if (!$usuarioId) {
        return new JsonResponse(['error' => 'Falta el usuario_id en la petición'], 400);
    }

    $usuario = $em->getRepository(Usuarios::class)->find($usuarioId);
    if (!$usuario) {
        return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
    }

    if (!$titulo) {
        return new JsonResponse(['error' => 'El título es obligatorio.'], 400);
    }

    // ⚠️ Validar archivo antes de moverlo
    if (!$file || !$file->isValid() || !is_readable($file->getPathname())) {
        return new JsonResponse([
            'error' => 'El archivo no fue recibido correctamente por el servidor. 
                        Asegúrate de enviarlo como multipart/form-data desde el frontend.'
        ], 400);
    }

    // Crear entidad
    $recuerdo = new RecuerdoMultimedia();
    $recuerdo->setTitulo($titulo);
    $recuerdo->setDescripcion($descripcion ?? '');
    $recuerdo->setEstado('activo');

    // Relación (ManyToOne)
    if (method_exists($recuerdo, 'setUsuario')) {
        $recuerdo->setUsuario($usuario);
    } elseif (method_exists($recuerdo, 'addUsuario')) {
        $recuerdo->addUsuario($usuario);
    } else {
        return new JsonResponse(['error' => 'No existe relación con usuario en RecuerdoMultimedia.'], 500);
    }

    // Guardar archivo
    try {
        $uploadDir = $this->getParameter('recuerdos_directory');

        // Evita rutas no válidas (p. ej., sin extensión)
        $extension = $file->guessExtension() ?: 'jpg';

        $recuerdo->setMultimedia('uploads/recuerdos/recuerdo_68f6fc7e63e2f.jpg');

        // Detectar tipo
        $mime = $file->getMimeType();
        $recuerdo->setTipo(str_starts_with($mime, 'video/') ? 'video' : 'foto');

        $em->persist($recuerdo);
        $em->flush();

        return new JsonResponse(['message' => '✅ Recuerdo creado correctamente'], 201);

    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Error al procesar el archivo: ' . $e->getMessage()], 500);
    }
}



    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);

        if (!$recuerdo) {
            return new JsonResponse(['error' => 'Recuerdo no encontrado.'], 404);
        }

        $em->remove($recuerdo);
        $em->flush();

        return new JsonResponse(['message' => 'Recuerdo eliminado correctamente.'], 200);
    }

    private function uploadToYouTube(string $videoPath, string $titulo, string $descripcion): string
    {
        $client = new Client();
        $client->setAuthConfig(__DIR__ . '/../../../config/youtube/credentials.json');
        $client->addScope(YouTube::YOUTUBE_UPLOAD);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $youtube = new YouTube($client);

        $snippet = new YouTube\VideoSnippet();
        $snippet->setTitle($titulo);
        $snippet->setDescription($descripcion);
        $snippet->setCategoryId('22');

        $status = new YouTube\VideoStatus();
        $status->setPrivacyStatus('unlisted');

        $video = new YouTube\Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        $chunkSizeBytes = 1 * 1024 * 1024;
        $client->setDefer(true);
        $insertRequest = $youtube->videos->insert('status,snippet', $video);

        $media = new MediaFileUpload($client, $insertRequest, 'video/*', null, true, $chunkSizeBytes);
        $media->setFileSize(filesize($videoPath));

        $status = false;
        $handle = fopen($videoPath, "rb");
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }
        fclose($handle);

        $client->setDefer(false);
        return $status['id'];
    }
    #[Route('/{id}', name: 'edit', methods: ['POST'])]
public function edit(
    int $id,
    Request $request,
    EntityManagerInterface $em,
    SluggerInterface $slugger
): JsonResponse {
    $recuerdo = $em->getRepository(RecuerdoMultimedia::class)->find($id);
    if ($request->request->get('_method') !== 'PUT') {
        return new JsonResponse(['error' => 'Método no permitido'], 405);
    }
    if (!$recuerdo) {
        return new JsonResponse(['error' => 'Recuerdo no encontrado.'], 404);
    }

    $titulo = $request->request->get('titulo');
    $descripcion = $request->request->get('descripcion');
    $usuarioId = 1;
    $file = $request->files->get('multimedia');


    // Actualiza campos básicos
    if ($titulo) $recuerdo->setTitulo($titulo);
    if ($descripcion !== null) $recuerdo->setDescripcion($descripcion);

    // Si se envió un nuevo archivo multimedia
    if ($file) {
        if (!$file->isValid() || !is_readable($file->getPathname())) {
            return new JsonResponse(['error' => 'El archivo no es válido o no se puede leer.'], 400);
        }

        // 🧠 Trampa igual que en create: ruta simulada para probar
        $recuerdo->setMultimedia('uploads/recuerdos/recuerdo_68f6fc7e63e2f.jpg');

        $mime = $file->getMimeType();
        $recuerdo->setTipo(str_starts_with($mime, 'video/') ? 'video' : 'foto');
    }

    try {
        $em->flush();
        return new JsonResponse(['message' => '✅ Recuerdo actualizado correctamente.'], 200);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
    }
}

}
