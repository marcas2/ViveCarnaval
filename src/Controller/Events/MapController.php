<?php

namespace App\Controller\Events; 

use App\Entity\Evento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    #[Route('/events/map', name: 'app_events_map')]
    public function index(EntityManagerInterface $em): Response
    {
        $eventos = $em->getRepository(Evento::class)->findAll();

        // Convertir a array serializable
        $eventosArray = array_map(function($evento) {
            return [
                'titulo' => $evento->getTitulo(),
                'descripcion' => $evento->getDescripcion(),
                'fecha' => $evento->getFecha() ? $evento->getFecha()->format('Y-m-d H:i:s') : null,
                'latitud' => $evento->getLatitud(),
                'longitud' => $evento->getLongitud(),
                'imagen' => $evento->getImagen(),
            ];
        }, $eventos);

        return $this->render('events/map/map.html.twig', [
            'eventos' => $eventosArray,
        ]);
    }
}
