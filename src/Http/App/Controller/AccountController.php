<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use App\Domain\Archer\Form\ArcherFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)]
class AccountController extends AbstractController
{
    public const ROUTE_APP_ACCOUNT = 'app_account';

    #[Route('/mon-compte', name: self::ROUTE_APP_ACCOUNT)]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArcherFormType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
        }

        return $this->render('/app/account/index.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(),
        ]);
    }
}
