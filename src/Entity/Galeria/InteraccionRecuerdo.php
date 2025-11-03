<?php

namespace App\Entity\Galeria;

use App\Entity\Login\Usuarios;
use App\Repository\Galeria\InteraccionRecuerdoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InteraccionRecuerdoRepository::class)]
class InteraccionRecuerdo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: RecuerdoMultimedia::class, inversedBy: 'interacciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RecuerdoMultimedia $recuerdo = null;

    #[ORM\ManyToOne(targetEntity: Usuarios::class, inversedBy: 'interacciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuarios $usuario = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    private ?string $tipo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenidoComentario = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    // --- Getters y Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecuerdo(): ?RecuerdoMultimedia
    {
        return $this->recuerdo;
    }

    public function setRecuerdo(?RecuerdoMultimedia $recuerdo): static
    {
        $this->recuerdo = $recuerdo;
        return $this;
    }

    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuarios $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getContenidoComentario(): ?string
    {
        return $this->contenidoComentario;
    }

    public function setContenidoComentario(?string $contenidoComentario): static
    {
        $this->contenidoComentario = $contenidoComentario;
        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;
        return $this;
    }
}
