<?php

namespace App\Entity;

use App\Repository\JournalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalRepository::class)]
class Journal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meal = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $nap = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $diaper_time = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $diaper_type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $activity = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(inversedBy: 'journals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getMeal(): ?string
    {
        return $this->meal;
    }

    public function setMeal(?string $meal): static
    {
        $this->meal = $meal;

        return $this;
    }

    public function getNap(): ?\DateTime
    {
        return $this->nap;
    }

    public function setNap(?\DateTime $nap): static
    {
        $this->nap = $nap;

        return $this;
    }

    public function getDiaperTime(): ?\DateTime
    {
        return $this->diaper_time;
    }

    public function setDiaperTime(?\DateTime $diaper_time): static
    {
        $this->diaper_time = $diaper_time;

        return $this;
    }

    public function getDiaperType(): ?string
    {
        return $this->diaper_type;
    }

    public function setDiaperType(?string $diaper_type): static
    {
        $this->diaper_type = $diaper_type;

        return $this;
    }

    public function getActivity(): ?string
    {
        return $this->activity;
    }

    public function setActivity(?string $activity): static
    {
        $this->activity = $activity;

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
