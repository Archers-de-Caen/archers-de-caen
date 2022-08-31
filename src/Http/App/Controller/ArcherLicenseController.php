<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use App\Domain\Archer\Form\ArcherLicenseFormType;
use App\Http\Security\Voter\ArcherLicenseVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArcherLicenseController extends AbstractController
{
    public const ROUTE_APP_LICENSE_LIST = 'app_license_list';
    public const ROUTE_APP_LICENSE_NEW = 'app_license_new';

    #[Route('/prendre-une-licence', name: self::ROUTE_APP_LICENSE_NEW)]
    #[IsGranted(ArcherLicenseVoter::CREATE, message: 'Vous devez compléter votre profil avant de pouvoir prendre une licence')]
    public function renewal(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArcherLicenseFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
        }

        return $this->render('/app/license/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/licences', name: self::ROUTE_APP_LICENSE_LIST)]
    public function licenses(): Response
    {
        return $this->render('/app/license/index.html.twig');
    }
}
