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

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\PrePersist] // ← CORRECTION : ajouté pour déclencher ce callback
    public function onPrePersist(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTimeImmutable();
        }
    }

    #[ORM\OneToMany(mappedBy: 'semainier', targetEntity: PlannedPresence::class)]
    private Collection $plannedPresences;

    public function __construct()
    {
        $this->plannedPresences = new ArrayCollection();
       
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

    public function removeAllPlannedPresences(EntityManagerInterface $em): void
    {
    foreach ($this->plannedPresences as $plannedPresence) {
        $em->remove($plannedPresence);
    }
    }
    public function addPlannedPresence(PlannedPresence $plannedPresence): static
    {
    if (!$this->plannedPresences->contains($plannedPresence)) {
        $this->plannedPresences->add($plannedPresence);
        $plannedPresence->setSemainier($this);
    }

    return $this;
}

/**
 * @return Collection<int, PlannedPresence>
 */
public function getPlannedPresences(): Collection
{
    return $this->plannedPresences;
}
}
