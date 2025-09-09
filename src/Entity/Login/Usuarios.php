<?php

namespace App\Entity\Login;

use App\Repository\Login\UsuariosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuariosRepository::class)]
#[ORM\Table(name: 'usuarios', schema: 'login')]

class Usuarios  implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $correo = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contrasena = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $foto = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $biografia = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Roles $rol = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): static
    {
        $this->correo = $correo;

        return $this;
    }

    public function getContrasena(): ?string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): static
    {
        $this->contrasena = $contrasena;

        return $this;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): static
    {
        $this->foto = $foto;

        return $this;
    }

    public function getBiografia(): ?string
    {
        return $this->biografia;
    }

    public function setBiografia(?string $biografia): static
    {
        $this->biografia = $biografia;

        return $this;
    }

    public function getRol(): ?Roles
    {
        return $this->rol;
    }

    public function setRol(?Roles $rol): static
    {
        $this->rol = $rol;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->correo; 
    }

    public function getRoles(): array
    {
        return [$this->rol ? $this->rol->getRol() : 'ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // Aquí limpiarías datos sensibles temporales si los hay
    }


    public function getPassword(): ?string
    {
        return $this->contrasena;
    }

}
