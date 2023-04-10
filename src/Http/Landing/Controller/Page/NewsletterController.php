<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Newsletter\NewsletterForm;
use App\Domain\Newsletter\NewsletterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/newsletter',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
class NewsletterController extends AbstractController
{
    public const ROUTE = 'landing_archer_newsletter';

    public function __invoke(
        Request $request,
        NewsletterRepository $newsletterRepository,
        ArcherRepository $archerRepository
    ): Response {
        $form = $this->createForm(NewsletterForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $licenseNumber */
            $licenseNumber = $form->get('licenseNumber')->getData();

            $archer = $archerRepository->findOneBy([
                'licenseNumber' => $licenseNumber,
            ]) ?? new Archer();

            /** @var array $types */
            $types = $form->get('types')->getData();

            /** @var string $email */
            $email = $form->get('email')->getData();

            if (null !== $archer->getEmail() && $archer->getEmail() !== $email) {
                $this->addFlash('error', 'Ce numéro de licence est associé à une adresse email différente !');

                return $this->redirectToRoute(self::ROUTE);
            }

            $archer
                ->setNewsletters($types)
                ->setEmail($email)
                ->setLicenseNumber($licenseNumber)
            ;

            $archerRepository->save($archer, true);

            if ($types) {
                $this->addFlash('success', 'Vous êtes bien inscrit à la newsletter.');
            } else {
                $this->addFlash('success', 'Votre désinscription a été prise en compte.');
            }
        }

        return $this->render('/landing/archers/newsletter.html.twig', [
            'form' => $form,
        ]);
    }
}
