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
            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message envoyé !');
            return $this->redirectToRoute('messages_inbox');
        }

        return $this->render('message/send.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/inbox', name: 'inbox')]
    public function inbox(MessageRepository $repo): Response
    {
        $messages = $repo->findBy(['recipient' => $this->getUser()], ['sent_at' => 'DESC']);

        return $this->render('message/inbox.html.twig', [
            'messages' => $messages
        ]);
    }
}