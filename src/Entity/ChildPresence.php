<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\ChildPresenceRepository;

#[ORM\Entity(repositoryClass: ChildPresenceRepository::class)]
class ChildPresence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $day = null;

    #[ORM\Column]
    private bool $present = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable:true)]
    private ?\DateTimeImmutable $arrivalTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable:true)]
    private ?\DateTimeImmutable $departureTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(inversedBy: 'childPresences')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Semainier $semainier = null;

    #[ORM\ManyToOne(inversedBy: 'childPresences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    public function getId(): ?int { return $this->id; }

    public function getDay(): ?\DateTimeImmutable { return $this->day; }

    public function setDay(\DateTimeImmutable $day): static
    {
        $this->day = $day;
        return $this;
    }

    public function isPresent(): bool { return $this->present; }

    public function setPresent(bool $present): self
    {
        $this->present = $present;
        return $this;
    }

    public function getArrivalTime(): ?\DateTimeImmutable { return $this->arrivalTime; }

    public function setArrivalTime(?\DateTimeImmutable $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    public function getDepartureTime(): ?\DateTimeImmutable { return $this->departureTime; }

    public function setDepartureTime(?\DateTimeImmutable $departureTime): static
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    public function getNote(): ?string { return $this->note; }

    public function setNote(?string $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getSemainier(): ?Semainier { return $this->semainier; }

    public function setSemainier(?Semainier $semainier): static
    {
        $this->semainier = $semainier;
        return $this;
    }

    public function getChild(): ?Child { return $this->child; }

    public function setChild(?Child $child): static
    {
        $this->child = $child;
        return $this;
    }
}

