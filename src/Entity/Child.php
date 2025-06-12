<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Icon;

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

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'children')]
    private Collection $users;

    /**
     * @var Collection<int, Allergy>
     */
    #[ORM\ManyToMany(targetEntity: Allergy::class, inversedBy: 'children')]
    private Collection $allergies;

    /**
     * @var Collection<int, SpecialDiet>
     */
    #[ORM\ManyToMany(targetEntity: SpecialDiet::class, inversedBy: 'children')]
    private Collection $specialDiets;

    #[ORM\ManyToOne(inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Group $childGroup = null;

    /**
     * @var Collection<int, Journal>
     */
    #[ORM\OneToMany(targetEntity: Journal::class, mappedBy: 'child')]
    private Collection $journals;

    /**
     * @var Collection<int, Journal>
     */
    #[ORM\OneToMany(targetEntity: Journal::class, mappedBy: 'children')]
    private Collection $journal;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->allergies = new ArrayCollection();
        $this->specialDiets = new ArrayCollection();
        $this->journals = new ArrayCollection();
        $this->journal = new ArrayCollection();
    }
    #[ORM\ManyToOne(targetEntity: Icon::class)]
    #[ORM\JoinColumn(name: "icons_id", referencedColumnName: "id", nullable: true)]
    private ?Icon $icon = null;

    public function getIcon(): ?Icon
    {
    return $this->icon;
    }

    public function setIcon(?Icon $icon): static
    {
    $this->icon = $icon;
    return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getMedicalNotes(): ?string
    {
        return $this->medicalNotes;
    }

    public function setMedicalNotes(?string $medicalNotes): static
    {
        $this->medicalNotes = $medicalNotes;

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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addChild($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeChild($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Allergy>
     */
    public function getAllergies(): Collection
    {
        return $this->allergies;
    }

    public function addAllergy(Allergy $allergy): static
    {
        if (!$this->allergies->contains($allergy)) {
            $this->allergies->add($allergy);
        }

        return $this;
    }

    public function removeAllergy(Allergy $allergy): static
    {
        $this->allergies->removeElement($allergy);

        return $this;
    }

    /**
     * @return Collection<int, SpecialDiet>
     */
    public function getSpecialDiets(): Collection
    {
        return $this->specialDiets;
    }

    public function addSpecialDiet(SpecialDiet $specialDiet): static
    {
        if (!$this->specialDiets->contains($specialDiet)) {
            $this->specialDiets->add($specialDiet);
        }

        return $this;
    }

    public function removeSpecialDiet(SpecialDiet $specialDiet): static
    {
        $this->specialDiets->removeElement($specialDiet);

        return $this;
    }
    public function getChildGroup(): ?Group
    {
        return $this->childGroup;
    }

    public function setChildGroup(?Group $childGroup): static
    {
        $this->childGroup = $childGroup;

        return $this;
    }

    /**
     * @return Collection<int, Journal>
     */
    public function getJournals(): Collection
    {
        return $this->journals;
    }

    public function addJournal(Journal $journal): static
    {
        if (!$this->journals->contains($journal)) {
            $this->journals->add($journal);
            $journal->setChild($this);
        }

        return $this;
    }

    public function removeJournal(Journal $journal): static
    {
        if ($this->journals->removeElement($journal)) {
            // set the owning side to null (unless already changed)
            if ($journal->getChild() === $this) {
                $journal->setChild(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Journal>
     */
    public function getJournal(): Collection
    {
        return $this->journal;
    }

}
