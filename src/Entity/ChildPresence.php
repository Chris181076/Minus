<?php

namespace App\Entity;

use App\Repository\ChildPresenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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
    private ?\DateTime $arrival_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $departure_time = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(inversedBy: 'childPresences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Semainier $semainier = null;

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
}
