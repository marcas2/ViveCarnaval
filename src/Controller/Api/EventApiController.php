<?php

namespace App\Controller\Api;

use App\Entity\Evento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/eventos')]
class EventApiController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tipo = $request->query->get('tipo');
        $desde = $request->query->get('desde');
        $hasta = $request->query->get('hasta');

        $qb = $em->getRepository(Evento::class)->createQueryBuilder('e');

        if ($tipo) {
            $qb->andWhere('e.tipo = :tipo')->setParameter('tipo', $tipo);
        }

        if ($desde) {
            $qb->andWhere('e.fecha >= :desde')->setParameter('desde', new \DateTime($desde));
        }

        if ($hasta) {
            $qb->andWhere('e.fecha <= :hasta')->setParameter('hasta', new \DateTime($hasta));
        }

        $qb->orderBy('e.fecha', 'ASC');
        $eventos = $qb->getQuery()->getResult();

        // Generar URLs absolutas para las imágenes
        $baseUrl = $request->getSchemeAndHttpHost();

        $data = array_map(fn(Evento $e) => [
            'id' => $e->getId(),
            'titulo' => $e->getTitulo(),
            'descripcion' => $e->getDescripcion(),
            'fecha' => $e->getFecha()->format('Y-m-d H:i'),
            'tipo' => $e->getTipo(),
            'latitud' => $e->getLatitud(),
            'longitud' => $e->getLongitud(),
            'imagen' => $e->getImagen()
                ? $baseUrl . '/uploads/events/' . $e->getImagen()
                : null,
        ], $eventos);

        return $this->json($data);
    }

    #[Route('/uploads/{filename}', name: 'api_image', methods: ['GET'])]
    public function getImage(string $filename): BinaryFileResponse
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/eventos/' . $filename;

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Imagen no encontrada.');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        return $response;
    }
}
