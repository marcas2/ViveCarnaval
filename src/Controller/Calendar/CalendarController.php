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
    $eventos = $repo->findAll();

    $data = array_map(function($evento) {
        return [
            'id' => $evento->getId(),
            'titulo' => $evento->getTitulo(),
            'descripcion' => $evento->getDescripcion(),
            'fecha' => $evento->getFecha()?->format(\DATE_ATOM), // ISO8601
            'ubicacion' => $evento->getUbicacion(),
        ];
    }, $eventos);

    return new JsonResponse($data);
}


    #[Route('/calendar', name: 'app_calendar')]
    public function index()
    {
        return $this->render('calendar/calendar/index.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }
}
