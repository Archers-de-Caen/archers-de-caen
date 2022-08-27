<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Archer\Form\ArcherLicenseFormType;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LicenseController extends AbstractController
{
    public const ROUTE_LANDING_LICENSE_FIRST_STEP = 'landing_license_first_step';
    public const ROUTE_LANDING_LICENSE_NEW = 'landing_license_new';
    public const ROUTE_LANDING_LICENSE_RENEWAL = 'landing_license_renewal';

    #[Route('/prendre-une-licence', name: self::ROUTE_LANDING_LICENSE_FIRST_STEP)]
    public function index(Request $request, ArcherRepository $archerRepository): Response
    {
        $errors = [];

        if (Request::METHOD_POST === $request->getMethod()) {
            $license = $request->request->get('license');

            if ('first-license' === $license || 'another-club' === $license) {
                return $this->redirectToRoute(self::ROUTE_LANDING_LICENSE_NEW);
            }

            if ('caen' === $license) {
                if ($licenseNumber = $request->request->get('license-number')) {
                    if ($archerRepository->findBy(['licenseNumber' => $licenseNumber])) {
                        return $this->redirectToRoute(self::ROUTE_LANDING_LICENSE_RENEWAL, [
                            'licenseNumber' => $licenseNumber,
                        ]);
                    }

                    $errors[] = 'Votre numéro de licence est introuvable, si vous êtes sûr de ne pas vous être trompé, contactez-nous';
                } else {
                    $errors[] = 'Si vous avez déjà était licencié, merci de nous fournir votre numéro de licence';
                }
            }
        }

        return $this->render('/landing/license/first-step.html.twig', [
            'errors' => $errors,
        ]);
    }

    #[Route('/prendre-une-licence/nouveau-aux-archers-de-caen', name: self::ROUTE_LANDING_LICENSE_NEW)]
    public function newCaen(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArcherLicenseFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
        }

        return $this->render('/landing/license/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/prendre-une-licence/renouvellement/{licenseNumber}', name: self::ROUTE_LANDING_LICENSE_RENEWAL)]
    public function renewal(Archer $archer, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArcherLicenseFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
        }

        return $this->render('/landing/license/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
