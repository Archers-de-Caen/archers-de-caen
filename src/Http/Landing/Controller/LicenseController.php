<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Archer\Form\ArcherFormType;
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
    public const ROUTE_LANDING_LICENSE = 'landing_license_new';
    public const ROUTE_LANDING_LICENSE_PERSONAL_INFORMATION = 'landing_license_personal_information';

    #[Route('/prendre-une-licence', name: self::ROUTE_LANDING_LICENSE_FIRST_STEP)]
    public function index(Request $request, ArcherRepository $archerRepository): Response
    {
        $errors = [];

        if (Request::METHOD_POST === $request->getMethod()) {
            $license = $request->request->get('license');

            if ('first-license' === $license || 'another-club' === $license) {
                return $this->redirectToRoute(self::ROUTE_LANDING_LICENSE);
            }

            if ('caen' === $license) {
                if ($licenseNumber = $request->request->get('license-number')) {
                    if ($archerRepository->findBy(['licenseNumber' => $licenseNumber])) {
                        return $this->redirectToRoute(self::ROUTE_LANDING_LICENSE_PERSONAL_INFORMATION, [
                            'licenseNumber' => $licenseNumber,
                            'type' => $request->query->get('type')
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

    #[Route('/prendre-une-licence/informations-personnel', name: self::ROUTE_LANDING_LICENSE_PERSONAL_INFORMATION)]
    public function personalInformation(Request $request, EntityManagerInterface $em): Response
    {
        if ($licenseNumber = $request->query->get('licenseNumber')) {
            $archer = $em->getRepository(Archer::class)->findOneBy(['licenseNumber' => $licenseNumber]);
        } else {
            $archer = new Archer();
        }

        $form = $this->createForm(ArcherFormType::class, $archer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($archer);
            $em->flush();

            return $this->redirectToRoute(self::ROUTE_LANDING_LICENSE, ['type' => $request->query->get('type')]);
        }

        return $this->render('/landing/license/personal_information.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/prendre-une-licence/{type}/{licenseNumber}', name: self::ROUTE_LANDING_LICENSE)]
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
