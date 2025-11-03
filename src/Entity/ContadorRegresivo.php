<?php

namespace App\Entity;

use App\Repository\ContadorRegresivoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContadorRegresivoRepository::class)]
#[ORM\Table(name: 'contador_regresivo', schema: 'calendar')]
class ContadorRegresivo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fechaInicioCarnaval = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $mensaje = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaInicioCarnaval(): ?\DateTime
    {
        return $this->fechaInicioCarnaval;
    }

    public function setFechaInicioCarnaval(?\DateTime $fechaInicioCarnaval): static
    {
        $this->fechaInicioCarnaval = $fechaInicioCarnaval;

        return $this;
    }

    public function getMensaje(): ?string
    {
        return $this->mensaje;
    }

    public function setMensaje(?string $mensaje): static
    {
        $this->mensaje = $mensaje;

        return $this;
    }
}
