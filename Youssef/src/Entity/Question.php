<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'id_Question', targetEntity: Choix::class)]
    private Collection $choixes;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    private ?User $user = null;

    public function __construct()
    {
        $this->choixes = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Choix>
     */
    public function getChoixes(): Collection
    {
        return $this->choixes;
    }

    public function addChoix(Choix $choix): static
    {
        if (!$this->choixes->contains($choix)) {
            $this->choixes->add($choix);
            $choix->setIdQuestion($this);
        }

        return $this;
    }

    public function removeChoix(Choix $choix): static
    {
        if ($this->choixes->removeElement($choix)) {
            // set the owning side to null (unless already changed)
            if ($choix->getIdQuestion() === $this) {
                $choix->setIdQuestion(null);
            }
        }

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
