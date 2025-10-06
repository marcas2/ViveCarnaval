<?php

namespace App\Repository;

use App\Entity\Evento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evento>
 */
class EventoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evento::class);
    }

    /**
     * Ejemplo de método personalizado para obtener próximos eventos
     */
    public function findProximosEventos(\DateTimeInterface $desde = null): array
    {
        $desde = $desde ?? new \DateTime();

        return $this->createQueryBuilder('e')
            ->andWhere('e.fecha >= :fecha')
            ->setParameter('fecha', $desde)
            ->orderBy('e.fecha', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
