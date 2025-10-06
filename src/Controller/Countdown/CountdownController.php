<?php

namespace App\Controller\Countdown;

use App\Entity\ContadorRegresivo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CountdownController extends AbstractController
{
    #[Route('/api/countdown', name: 'api_countdown', methods: ['GET'])]
    public function countdown(EntityManagerInterface $em): JsonResponse
    {
        $contador = $em->getRepository(ContadorRegresivo::class)->findOneBy([], ['id' => 'DESC']); // último registro

        if (!$contador || !$contador->getFechaInicioCarnaval()) {
            return new JsonResponse(['error' => 'No hay fecha configurada'], 404);
        }

        $fechaInicio = $contador->getFechaInicioCarnaval();
        $mensaje = $contador->getMensaje() ?? '¡El Carnaval está por comenzar! 🎉';

        return new JsonResponse([
            'fechaInicio' => $fechaInicio->format(\DateTimeInterface::ATOM),
            'mensaje' => $mensaje,
        ]);
    }
}
