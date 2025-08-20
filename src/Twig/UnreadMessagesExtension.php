<?php
// src/Twig/UnreadMessagesExtension.php

namespace App\Twig;

use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UnreadMessagesExtension extends AbstractExtension
{
    private MessageRepository $messageRepository;
    private Security $security;

    public function __construct(MessageRepository $messageRepository, Security $security)
    {
        $this->messageRepository = $messageRepository;
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this, 'getUnreadMessagesCount']),
            new TwigFunction('test_function', [$this, 'testFunction']), 
        ];
    }

    public function testFunction(): string
    {
        return "Extension Twig fonctionne !";
    }

    public function getUnreadMessagesCount(): int
    {
        try {
            $user = $this->security->getUser();
            
            if (!$user) {
                return 0;
            }

            return $this->messageRepository->count([
                'recipient' => $user,
                'is_read' => false
            ]);
        } catch (\Exception $e) {
            return 0;
        }
    }
}