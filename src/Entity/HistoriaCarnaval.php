<?php

namespace App\Entity;

use App\Repository\HistoriaCarnavalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriaCarnavalRepository::class)]
#[ORM\Table(name: 'historia_carnaval', schema: 'historia')]
class HistoriaCarnaval
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $titulo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenido = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenido2 = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenido3 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $imagen = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): static
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getContenido2(): ?string
    {
        return $this->contenido2;
    }

    public function setContenido2(?string $contenido2): static
    {
        $this->contenido2 = $contenido2;

        return $this;
    }

    public function getContenido3(): ?string
    {
        return $this->contenido3;
    }

    public function setContenido3(?string $contenido3): static
    {
        $this->contenido3 = $contenido3;

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(string $imagen): static
    {
        $this->imagen = $imagen;

        return $this;
    }
}
