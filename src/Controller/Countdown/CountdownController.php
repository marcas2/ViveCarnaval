<?php
namespace App\Controller\Countdown;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CountdownController extends AbstractController
{
    #[Route('/api/countdown', name: 'api_countdown', methods: ['GET'])]
    public function countdown(): JsonResponse
    {
        // Lee la env de manera robusta
        $start = $_ENV['CARNIVAL_START'] ?? $_SERVER['CARNIVAL_START'] ?? getenv('CARNIVAL_START') ?: null;

        if (!$start) {
            return new JsonResponse(['error' => 'Start date not configured'], 500);
        }

        // valida y normaliza la fecha a formato ISO8601 (DATE_ATOM)
        try {
            $dt = new \DateTimeImmutable($start);
            return new JsonResponse(['start' => $dt->format(\DATE_ATOM)]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid start date: ' . $e->getMessage()], 500);
        }
    }
}
