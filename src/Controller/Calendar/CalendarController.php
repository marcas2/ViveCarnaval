<?php
// src/Controller/Calendar/CalendarController.php
namespace App\Controller\Calendar;

use App\Entity\Evento;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index()
    {
        return $this->render('calendar/index.html.twig');
    }

    #[Route('/api/events', name: 'app_calendar_api_events', methods: ['GET'])]
    public function apiEventos(EntityManagerInterface $em): JsonResponse
    {
        $repo = $em->getRepository(Evento::class);
        $eventos = $repo->findAll();

        $data = [];

        foreach ($eventos as $evento) {
            $data[] = [
                'id' => $evento->getId(),
                'title' => $evento->getTitulo(),
                'start' => $evento->getFecha()->format('Y-m-d'),
                'description' => $evento->getDescripcion(),
                'tipo' => $evento->getTipo(),
                'imagen' => $evento->getImagen()
                    ? '/uploads/events/' . $evento->getImagen()
                    : null,
            ];
        }

        return new JsonResponse($data);
    }
}
