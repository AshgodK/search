<?php

namespace App\Entity;

use App\Repository\ChoixRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChoixRepository::class)]
class Choix
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $rep_q = null;

    #[ORM\ManyToOne(inversedBy: 'choixes')]
    private ?Question $id_Question = null;

    #[ORM\ManyToOne(inversedBy: 'choixes')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRepQ(): ?string
    {
        return $this->rep_q;
    }

    public function setRepQ(string $rep_q): static
    {
        $this->rep_q = $rep_q;

        return $this;
    }

    public function getIdQuestion(): ?Question
    {
        return $this->id_Question;
    }

    public function setIdQuestion(?Question $id_Question): static
    {
        $this->id_Question = $id_Question;

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
}
