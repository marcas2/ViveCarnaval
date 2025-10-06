<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'evento', schema: 'calendar')]
class Evento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length:255)]
    private ?string $titulo = null;

    #[ORM\Column(type: 'text', nullable:true)]
    private ?string $descripcion = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitud = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitud = null;

    #[ORM\Column(length:255, nullable:true)]
    private ?string $imagen = null;
    #[ORM\Column(length:100, nullable:true)]
    private ?string $tipo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }
    public function getLatitud(): ?float 
    { 
        return $this->latitud; 
    }
    public function setLatitud(?float $latitud): self 
    { 
        $this->latitud = $latitud; return $this; 
    }

    public function getLongitud(): ?float 
    { 
        return $this->longitud; 
    }
    public function setLongitud(?float $longitud): self 
    { 
        $this->longitud = $longitud; 
        return $this;
    }
    
    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(?string $imagen): self
    {
        $this->imagen = $imagen;
        return $this;
    }
    
    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;
        return $this;
    }

    
}