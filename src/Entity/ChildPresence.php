<?php

namespace App\Entity;

use App\Repository\ChildPresenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\child;

#[ORM\Entity(repositoryClass: ChildPresenceRepository::class)]
class ChildPresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $day = null;

    #[ORM\Column]
    private ?bool $present = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeImmutable $arrival_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeImmutable $departure_time = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(inversedBy: 'childPresences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semainier $semainier = null;

    #[ORM\ManyToOne(inversedBy: 'relation')]
    private ?Child $child = null;

    /**
     * @var Collection<int, Child>
     */
    #[ORM\OneToMany(targetEntity: Child::class, mappedBy: 'childPresence')]
    private Collection $relation;
  

    public function __construct()
    {
        $this->relation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): ?\DateTime
    {
        return $this->day;
    }

    public function setDay(\DateTime $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function isPresent(): ?bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): static
    {
        $this->present = $present;

        return $this;
    }

    public function getArrivalTime(): ?\DateTime
    {
        return $this->arrival_time;
    }

    public function setArrivalTime(?\DateTimeInterface $arrival_time): static
    {
        $this->arrival_time = $arrival_time;

        return $this;
    }

    public function getDepartureTime(): ?\DateTime
    {
        return $this->departure_time;
    }

    public function setDepartureTime(\DateTimeImmutable $departure_time): static
    {
        $this->departure_time = $departure_time;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getSemainier(): ?Semainier
    {
        return $this->semainier;
    }

    public function setSemainier(?Semainier $semainier): static
    {
        $this->semainier = $semainier;

        return $this;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;

        return $this;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getRelation(): Collection
    {
        return $this->relation;
    }

    public function addRelation(Child $relation): static
    {
        if (!$this->relation->contains($relation)) {
            $this->relation->add($relation);
            $relation->setChildPresence($this);
        }

        return $this;
    }

    public function removeRelation(Child $relation): static
    {
        if ($this->relation->removeElement($relation)) {
            // set the owning side to null (unless already changed)
            if ($relation->getChildPresence() === $this) {
                $relation->setChildPresence(null);
            }
        }

        return $this;
    }
}
