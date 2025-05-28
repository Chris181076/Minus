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
    private ?\DateTimeImmutable $week_day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $arrival_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $departure_time = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeekDay(): ?\DateTimeImmutable
    {
        return $this->week_day;
    }

    public function setWeekDay(\DateTimeImmutable $week_day): static
    {
        $this->week_day = $week_day;

        return $this;
    }

    public function getArrivalTime(): ?\DateTime
    {
        return $this->arrival_time;
    }

    public function setArrivalTime(\DateTime $arrival_time): static
    {
        $this->arrival_time = $arrival_time;

        return $this;
    }

    public function getDepartureTime(): ?\DateTime
    {
        return $this->departure_time;
    }

    public function setDepartureTime(\DateTime $departure_time): static
    {
        $this->departure_time = $departure_time;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
