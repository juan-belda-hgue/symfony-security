<?php

namespace App\Entity;

use App\Repository\ProfesionalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ProfesionalRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_NIF', fields: ['nif'])]
class Profesional implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $nif = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $ape1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ape2 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sexo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $numero_colegiado = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $fecha_nacimiento = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telefono2 = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telefono3 = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telefono4 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poblacion = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $cp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $provincia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localidad_nacimiento = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $nss = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre_padre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nombre_madre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nacionalidad = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comunidad_autonoma = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(string $nif): static
    {
        $this->nif = $nif;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->nif;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getApe1(): ?string
    {
        return $this->ape1;
    }

    public function setApe1(string $ape1): static
    {
        $this->ape1 = $ape1;

        return $this;
    }

    public function getApe2(): ?string
    {
        return $this->ape2;
    }

    public function setApe2(?string $ape2): static
    {
        $this->ape2 = $ape2;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(?string $sexo): static
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getNumeroColegiado(): ?string
    {
        return $this->numero_colegiado;
    }

    public function setNumeroColegiado(?string $numero_colegiado): static
    {
        $this->numero_colegiado = $numero_colegiado;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeImmutable
    {
        return $this->fecha_nacimiento;
    }

    public function setFechaNacimiento(?\DateTimeImmutable $fecha_nacimiento): static
    {
        $this->fecha_nacimiento = $fecha_nacimiento;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getTelefono2(): ?string
    {
        return $this->telefono2;
    }

    public function setTelefono2(?string $telefono2): static
    {
        $this->telefono2 = $telefono2;

        return $this;
    }

    public function getTelefono3(): ?string
    {
        return $this->telefono3;
    }

    public function setTelefono3(?string $telefono3): static
    {
        $this->telefono3 = $telefono3;

        return $this;
    }

    public function getTelefono4(): ?string
    {
        return $this->telefono4;
    }

    public function setTelefono4(?string $telefono4): static
    {
        $this->telefono4 = $telefono4;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): static
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getPoblacion(): ?string
    {
        return $this->poblacion;
    }

    public function setPoblacion(?string $poblacion): static
    {
        $this->poblacion = $poblacion;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(?string $provincia): static
    {
        $this->provincia = $provincia;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLocalidadNacimiento(): ?string
    {
        return $this->localidad_nacimiento;
    }

    public function setLocalidadNacimiento(?string $localidad_nacimiento): static
    {
        $this->localidad_nacimiento = $localidad_nacimiento;

        return $this;
    }

    public function getNss(): ?string
    {
        return $this->nss;
    }

    public function setNss(?string $nss): static
    {
        $this->nss = $nss;

        return $this;
    }

    public function getNombrePadre(): ?string
    {
        return $this->nombre_padre;
    }

    public function setNombrePadre(?string $nombre_padre): static
    {
        $this->nombre_padre = $nombre_padre;

        return $this;
    }

    public function getNombreMadre(): ?string
    {
        return $this->nombre_madre;
    }

    public function setNombreMadre(?string $nombre_madre): static
    {
        $this->nombre_madre = $nombre_madre;

        return $this;
    }

    public function getNacionalidad(): ?string
    {
        return $this->nacionalidad;
    }

    public function setNacionalidad(?string $nacionalidad): static
    {
        $this->nacionalidad = $nacionalidad;

        return $this;
    }

    public function getComunidadAutonoma(): ?string
    {
        return $this->comunidad_autonoma;
    }

    public function setComunidadAutonoma(?string $comunidad_autonoma): static
    {
        $this->comunidad_autonoma = $comunidad_autonoma;

        return $this;
    }
}
