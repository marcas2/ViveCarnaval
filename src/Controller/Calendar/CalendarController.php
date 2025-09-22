<?php
// src/Controller/Calendar/CalendarController.php
namespace App\Controller\Calendar;

use App\Repository\EventoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/api/events', name: 'api_events', methods: ['GET'])]
    public function events(EventoRepository $repo): JsonResponse
    {
        $events = $repo->findAll();

        $data = array_map(function($e) {
            return [
                'id'          => $e->getId(),
                'title'       => $e->getTitulo(),
                'date'         => $e->getFecha()?->format('Y-m-d\TH:i:s'),
                'description' => $e->getDescripcion(),
                'ubicacion'   => $e->getUbicacion(),
            ];
        }, $events);

        return $this->json($data);
    }

    #[Route('/calendar', name: 'app_calendar')]
    public function index()
    {
        return $this->render('calendar/calendar/index.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }
}
