<?php

namespace App\Entity\Galeria;

use App\Entity\Login\Usuarios;
use App\Repository\Galeria\RecuerdoMultimediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecuerdoMultimediaRepository::class)]
#[ORM\Table(name: 'recuerdo_multimedia', schema: 'gallery')]
class RecuerdoMultimedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Usuarios>
     */
    #[ORM\ManyToOne(targetEntity: Usuarios::class, inversedBy: 'recuerdos')]
    private ?Usuarios $usuario = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $tipo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titulo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $multimedia = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Usuarios|null
     */
    public function getUsuario(): ?Usuarios
    {
        return $this->usuario;
    }

    public function addUsuario(Usuarios $usuario): static
    {
        if ($this->usuario !== $usuario) {
            $this->usuario = $usuario;
        }

        return $this;
    }

    public function removeUsuario(Usuarios $usuario): static
    {
        if ($this->usuario === $usuario) {
            $this->usuario = null;
        }

        return $this;
    }


    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getMultimedia(): ?string
    {
        return $this->multimedia;
    }
    public function setMultimedia(string $multimedia): static
    {
        $this->multimedia = $multimedia;

        return $this;
    }
    #[ORM\OneToMany(mappedBy: 'recuerdo', targetEntity: InteraccionRecuerdo::class, cascade: ['remove'])]
    private Collection $interacciones;

}
