<?php



// src/Controller/ContactController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Form\ContactType;



use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]


    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from($data['email'])
                ->to('recipient@example.com')
                ->subject($data['sujet'])
                ->html("
                    <h2>Message de contact</h2>
                    <p><strong>Nom :</strong> {$data['nom']}</p>
                    <p><strong>Email :</strong> {$data['email']}</p>
                    <p><strong>Message :</strong><br>{$data['message']}</p>
                ");

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
