<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuiller saisir un nom')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuiller saisir une introduction')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'Veuiller saisir une echeance')]
    private ?\DateTimeInterface $echances = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuiller saisir un statut')]
    private ?string $statut = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuiller saisir un prix')]
    private ?float $prix = null;

    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: Demande::class, cascade: ['remove'])]
    private Collection $demandes;

    #[ORM\ManyToOne(inversedBy: 'offres')]
    private ?User $idUser = null;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
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

    public function getEchances(): ?\DateTimeInterface
    {
        return $this->echances;
    }

    public function setEchances(\DateTimeInterface $echances): static
    {
        $this->echances = $echances;

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

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setOffre($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getOffre() === $this) {
                $demande->setOffre(null);
            }
        }

        return $this;
    }

    public function getIdUser(): ?User
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): static
    {
        $this->idUser = $idUser;

        return $this;
    }
    public function findByidUser($userId)
    {
        return $this->createQueryBuilder('o')
            ->select('o.id')
            ->andWhere('o.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

}