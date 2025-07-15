<?php

namespace App\Entity;

use App\Repository\PlannedPresenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlannedPresenceRepository::class)]
class PlannedPresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $week_day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $arrival_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $departure_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable:false)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
    if ($this->created_at === null) {
        $this->created_at = new \DateTimeImmutable();
    }
    }

    #[ORM\ManyToOne(inversedBy: 'plannedPresences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\ManyToOne(targetEntity: Semainier::class, inversedBy: 'plannedPresences')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Semainier $semainier = null;


    public function getSemainier(): ?Semainier
    {
    return $this->semainier;
    }

    public function setSemainier(?Semainier $semainier): static
    {
    $this->semainier = $semainier;
    return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeekDay(): ?string
    {
        return $this->week_day;
    }

    public function setWeekDay(string $week_day): static
    {
        $this->week_day = $week_day;

        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrival_time;
    }

    public function setArrivalTime(?\DateTimeInterface $arrival_time): static
    {
        $this->arrival_time = $arrival_time;

        return $this;
    }

    public function getDepartureTime(): ?\DateTimeInterface
    {
        return $this->departure_time;
    }

    public function setDepartureTime(?\DateTimeInterface $departure_time): static
    {
        $this->departure_time = $departure_time;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

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


}
