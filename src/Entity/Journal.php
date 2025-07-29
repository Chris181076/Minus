<?php

namespace App\Entity;

use App\Repository\JournalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: JournalRepository::class)]
class Journal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(inversedBy: 'journal')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\OneToMany(mappedBy: 'journal', targetEntity: JournalEntry::class, cascade: ['persist', 'remove'])]
    private Collection $entries;
    
    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->date = new \DateTimeImmutable();
    }
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(JournalEntry $entry): self
    {
        if (!$this->entries->contains($entry)) {
            $this->entries[] = $entry;
            $entry->setJournal($this);
        }
        return $this;
    }

    public function removeEntry(JournalEntry $entry): self
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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

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
