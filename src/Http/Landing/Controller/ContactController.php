<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Contact\Form\ContactForm;
use App\Domain\Contact\Model\ContactRequest;
use App\Domain\Contact\Service\ContactService;
use App\Domain\Contact\TooManyContactException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'landing_contact')]
    public function contact(Request $request, EntityManagerInterface $em, ContactService $contactService): Response
    {
        $contact = new ContactRequest();
        $contactForm = $this->createForm(ContactForm::class, $contact);

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            try {
                $contactService->send($contact, $request->getClientIp());

                $em->persist($contact);
                $em->flush();

                $this->addFlash('success', 'Votre message a bien été envoyé.');
            } catch (TooManyContactException) {
                $this->addFlash('error', 'Vous avez déjà envoyé un message, merci de patienter.');
            } catch (TransportExceptionInterface) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message.');
            }
        }

        return $this->render('/landing/contact/contact.html.twig', [
            'form' => $contactForm->createView(),
        ]);
    }
}
