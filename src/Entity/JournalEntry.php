<?php

namespace App\Entity;

use App\Repository\JournalEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: JournalEntryRepository::class)]
class JournalEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $heure = null;

    #[ORM\Column(length: 150)]
    private ?string $action = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Journal $journal = null;

    #[ORM\OneToMany(mappedBy: 'journal', targetEntity: JournalEntry::class, cascade: ['persist', 'remove'])]
    private Collection $entries;

        public function __construct()
        {
            $this->entries = new ArrayCollection();
        }

        public function getEntries(): Collection
        {
            return $this->entries;
        }
            public function addEntry(JournalEntry $entry): static
        {
            if (!$this->entries->contains($entry)) {
                $this->entries->add($entry);
                $entry->setJournal($this);
            }

            return $this;
        }

        public function removeEntry(JournalEntry $entry): static
        {
            if ($this->entries->removeElement($entry)) {
                // set the owning side to null (unless already changed)
                if ($entry->getJournal() === $this) {
                    $entry->setJournal(null);
                }
            }

            return $this;
        }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeure(): ?\DateTimeImmutable
    {
        return $this->heure;
    }

    public function setHeure(?\DateTimeImmutable $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getJournal(): ?Journal
    {
        return $this->journal;
    }

    public function setJournal(?Journal $journal): static
    {
        $this->journal = $journal;

        return $this;
    }
}
