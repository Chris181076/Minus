<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sent_at = null;

    #[ORM\Column]
    private ?bool $is_read = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sentMessages')]
    #[ORM\JoinTable(name: 'message_user_sender')]
    private Collection $sender;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'receivedMessages')]
    #[ORM\JoinTable(name: 'message_user_recipient')]
    private Collection $recipient;

    public function __construct()
    {
        $this->sender = new ArrayCollection();
        $this->recipient = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sent_at;
    }

    public function setSentAt(\DateTimeImmutable $sent_at): static
    {
        $this->sent_at = $sent_at;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->is_read;
    }

    public function setIsRead(bool $is_read): static
    {
        $this->is_read = $is_read;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getSender(): Collection
    {
        return $this->sender;
    }

    public function addSender(User $sender): static
    {
        if (!$this->sender->contains($sender)) {
            $this->sender->add($sender);
        }

        return $this;
    }

    public function removeSender(User $sender): static
    {
        $this->sender->removeElement($sender);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRecipient(): Collection
    {
        return $this->recipient;
    }

    public function addRecipient(User $recipient): static
    {
        if (!$this->recipient->contains($recipient)) {
            $this->recipient->add($recipient);
        }

        return $this;
    }

    public function removeRecipient(User $recipient): static
    {
        $this->recipient->removeElement($recipient);

        return $this;
    }
}
