<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use App\Domain\License\Form\ArcherLicenseFormType;
use App\Domain\Archer\Model\Archer;
use App\Domain\License\Model\ArcherLicense;
use App\Domain\Billing\Config\PaymentMethod;
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
        $archerLicense = new ArcherLicense();
        $form = $this->createForm(ArcherLicenseFormType::class, $archerLicense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Archer $archer */
            $archer = $this->getUser();

            $archerLicense->setArcher($archer);
            $archerLicense->setPrice($archerLicense->getLicense()?->getPrice());

            $em->persist($archerLicense);
            $em->flush();

            if (PaymentMethod::BANK_CARD === $archerLicense->getPaymentMethod()) {
                return $this->redirect('hello-assos.fr ou page de info paiement');
            }

            $this->addFlash('success', 'Votre demande de licence à bien été prit en compte');

            return $this->redirectToRoute(self::ROUTE_APP_LICENSE_LIST);
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
