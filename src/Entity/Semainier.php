<?php

namespace App\Entity;

use App\Repository\SemainierRepository;
use App\Entity\Child;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ChildRepository;
use App\Controller\ChildPresenceRepository;

#[ORM\Entity(repositoryClass: SemainierRepository::class)]
#[ORM\HasLifecycleCallbacks] // ← CORRECTION : placée ici, sur la classe
class Semainier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date_immutable', unique: true)]
    private ?\DateTimeImmutable $week_start_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\PrePersist] // ← CORRECTION : ajouté pour déclencher ce callback
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
    }

    /**
     * @var Collection<int, ChildPresence>
     */
    #[ORM\OneToMany(targetEntity: ChildPresence::class, mappedBy: 'semainier')]
    private Collection $childPresences;

    public function __construct()
    {
        $this->childPresences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeekStartDate(): ?\DateTimeImmutable
    {
        return $this->week_start_date;
    }

    public function setWeekStartDate(\DateTimeImmutable $week_start_date): static
    {
        $this->week_start_date = $week_start_date;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return Collection<int, ChildPresence>
     */
    public function getChildPresences(): Collection
    {
        return $this->childPresences;
    }

    public function addChildPresence(ChildPresence $childPresence): static
    {
        if (!$this->childPresences->contains($childPresence)) {
            $this->childPresences->add($childPresence);
            $childPresence->setSemainier($this);
        }

        return $this;
    }

    public function removeChildPresence(ChildPresence $childPresence): static
    {
        if ($this->childPresences->removeElement($childPresence)) {
            if ($childPresence->getSemainier() === $this) {
                $childPresence->setSemainier(null);
            }
        }

        return $this;
    }
}
