<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'Date cannot be blank')]    
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Statut cannot be blank')]
    #[Assert\Length(max: 50, maxMessage: 'Statut content cannot be longer than {{ limit }} characters')]
    private ?string $statut = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Price cannot be blank')]
    #[Assert\Range(
        notInRangeMessage: 'Price must be between {{ min }} and {{ max }}',
        invalidMessage: 'Price must be a number',
        min: 0,
        max: 100
    )]
    private ?float $prix = null;



    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'commande')]
    private ?Service $service = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }
}
