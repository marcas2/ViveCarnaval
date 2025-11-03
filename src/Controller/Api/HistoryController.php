<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\HistoriaCarnaval;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/history')]
final class HistoryController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $em): Response
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
        $baseUrl = $request->getSchemeAndHttpHost();

        $data = array_map(fn(HistoriaCarnaval $h) => [
            'id' => $h->getId(),
            'titulo' => $h->getTitulo(),
            'contenido' => $h->getContenido(),
            'contenido2' => $h->getContenido2(),
            'contenido3' => $h->getContenido3(),
            'imagen' => $h->getImagen()
                ? $baseUrl . '/uploads/history/' . $h->getImagen()
                : null,
        ], $historias);
        return $this->json($data);
    }
}
