<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ChildRepository::class)]
class Child
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $medicalNotes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToMany(targetEntity: Allergy::class, inversedBy: 'children')]
    private Collection $allergies;

    #[ORM\ManyToMany(targetEntity: SpecialDiet::class, inversedBy: 'children')]
    private Collection $specialDiets;

    #[ORM\ManyToOne(inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Group $childGroup = null;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: Journal::class, cascade: ['persist', 'remove'])]
    private Collection $journals;

    #[ORM\ManyToOne(targetEntity: Icon::class)]
    #[ORM\JoinColumn(name: 'icons_id', referencedColumnName: 'id', nullable: true)]
    private ?Icon $icon = null;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: ChildPresence::class, cascade: ['persist', 'remove'])]
    private Collection $childPresences;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: PlannedPresence::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $plannedPresences;

    #[ORM\ManyToOne(inversedBy: 'Children')]
    private ?User $user = null;

    public function __construct()
    {
        $this->allergies = new ArrayCollection();
        $this->specialDiets = new ArrayCollection();
        $this->journals = new ArrayCollection();
        $this->childPresences = new ArrayCollection();
        $this->plannedPresences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getMedicalNotes(): ?string
    {
        return $this->medicalNotes;
    }

    public function setMedicalNotes(?string $medicalNotes): self
    {
        $this->medicalNotes = $medicalNotes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getChildGroup(): ?Group
    {
        return $this->childGroup;
    }

    public function setChildGroup(?Group $group): self
    {
        $this->childGroup = $group;
        return $this;
    }

    public function getIcon(): ?Icon
    {
        return $this->icon;
    }

    public function setIcon(?Icon $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /** @return Collection<int, Allergy> */
    public function getAllergies(): Collection
    {
        return $this->allergies;
    }

    public function addAllergy(Allergy $allergy): self
    {
        if (!$this->allergies->contains($allergy)) {
            $this->allergies->add($allergy);
        }
        return $this;
    }

    public function removeAllergy(Allergy $allergy): self
    {
        $this->allergies->removeElement($allergy);
        return $this;
    }

    /** @return Collection<int, SpecialDiet> */
    public function getSpecialDiets(): Collection
    {
        return $this->specialDiets;
    }

    public function addSpecialDiet(SpecialDiet $diet): self
    {
        if (!$this->specialDiets->contains($diet)) {
            $this->specialDiets->add($diet);
        }
        return $this;
    }

    public function removeSpecialDiet(SpecialDiet $diet): self
    {
        $this->specialDiets->removeElement($diet);
        return $this;
    }

    /** @return Collection<int, Journal> */
    public function getJournals(): Collection
    {
        return $this->journals;
    }

    public function addJournal(Journal $journal): self
    {
        if (!$this->journals->contains($journal)) {
            $this->journals->add($journal);
            $journal->setChild($this);
        }
        return $this;
    }

    public function removeJournal(Journal $journal): self
    {
        if ($this->journals->removeElement($journal) && $journal->getChild() === $this) {
            $journal->setChild(null);
        }
        return $this;
    }

    /** @return Collection<int, ChildPresence> */
    public function getChildPresences(): Collection
    {
        return $this->childPresences;
    }

    public function addChildPresence(ChildPresence $presence): self
    {
        if (!$this->childPresences->contains($presence)) {
            $this->childPresences->add($presence);
            $presence->setChild($this);
        }
        return $this;
    }

    public function removeChildPresence(ChildPresence $presence): self
    {
        if ($this->childPresences->removeElement($presence) && $presence->getChild() === $this) {
            $presence->setChild(null);
        }
        return $this;
    }

    /** @return Collection<int, PlannedPresence> */
    public function getPlannedPresences(): Collection
    {
        return $this->plannedPresences;
    }

    public function addPlannedPresence(PlannedPresence $presence): self
    {
        if (!$this->plannedPresences->contains($presence)) {
            $this->plannedPresences->add($presence);
            $presence->setChild($this);
        }
        return $this;
    }

    public function removePlannedPresence(PlannedPresence $presence): self
    {
        if ($this->plannedPresences->removeElement($presence) && $presence->getChild() === $this) {
            $presence->setChild(null);
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
