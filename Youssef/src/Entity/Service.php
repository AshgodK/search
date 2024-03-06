<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name cannot be blank')]
    #[Assert\Length(max: 50, maxMessage: 'Name content cannot be longer than {{ limit }} characters')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Description cannot be blank')]
    #[Assert\Regex(
        pattern: '/^\D*$/',
        message: 'Description cannot contain numbers'
    )]
    #[Assert\Length(max: 1000, maxMessage: 'Description content cannot be longer than {{ limit }} characters')]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Disponibilte cannot be blank')]
    #[Assert\Length(max: 3, maxMessage: 'Availability content cannot be longer than {{ limit }} characters')]
    private ?string $disponibilite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Categorie cannot be blank')]
    #[Assert\Length(max: 20, maxMessage: 'Categorie content cannot be longer than {{ limit }} characters')]
    private ?string $categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Commande::class)]
    private Collection $commande;

    public function __construct()
    {
        $this->commande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        // Add a null check to handle null values gracefully
        if ($image !== null) {
            $this->image = $image;
        }

        return $this;
    }
        public function getDisponibilite(): ?string
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(string $disponibilite): static
    {
        $this->disponibilite = $disponibilite;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

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

public function __toString() {
        return $this->getNom(); // Or whatever method returns the description
    }

/**
 * @return Collection<int, Commande>
 */
public function getCommande(): Collection
{
    return $this->commande;
}

public function addCommande(Commande $commande): static
{
    if (!$this->commande->contains($commande)) {
        $this->commande->add($commande);
        $commande->setService($this);
    }

    return $this;
}

public function removeCommande(Commande $commande): static
{
    if ($this->commande->removeElement($commande)) {
        // set the owning side to null (unless already changed)
        if ($commande->getService() === $this) {
            $commande->setService(null);
        }
    }

    return $this;
}

}
