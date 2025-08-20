<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\MessageRepository;
use App\Form\MessageForm;


#[Route('/messages', name: 'messages_')]
class MessageController extends AbstractController
{
    #[Route('/send', name: 'send')]
    public function send(Request $request, EntityManagerInterface $em): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageForm::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($this->getUser());
            $message->setSent_at(new \DateTimeImmutable());
            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message envoyé !');
           return $this->redirectToRoute('messages_send');
        }
        $sentMessages = $em->getRepository(Message::class)
            ->findBy(['sender' => $this->getUser()], ['sent_at' => 'DESC']);
        return $this->render('message/send.html.twig', [
            'form' => $form->createView(),
            'sentMessages' => $sentMessages,
        ]);
    }

    #[Route('/inbox', name: 'inbox')]
    public function inbox(MessageRepository $repo, EntityManagerInterface $em): Response
    {
    $messages = $repo->findBy(['recipient' => $this->getUser()], ['sent_at' => 'DESC']);

    $hasChanges = false;
    foreach ($messages as $message) {
        if ($message->getRecipient() === $this->getUser() && !$message->isRead()) {
            $message->setIsRead(true);
            $hasChanges = true;
        }
    }
    
    if ($hasChanges) {
        $em->flush();
    }

    $unreadCount = $repo->count([
        'recipient' => $this->getUser(),
        'is_read' => false
    ]);

    return $this->render('message/inbox.html.twig', [
        'messages' => $messages,
        'unreadCount' => $unreadCount
    ]);
    }

    #[Route('/{id}', name: 'message_read')]
    public function readMessage(Message $message, EntityManagerInterface $em): Response
    {
    
    if ($message->getRecipient() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }
    
    if (!$message->isRead()) {
        $message->setIsRead(true);
        $em->flush();
    }

    return $this->redirectToRoute('messages_inbox');
    }

}