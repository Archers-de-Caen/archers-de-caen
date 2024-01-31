<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use App\Domain\Archer\Form\RegistrationFormType;
use App\Domain\Archer\Model\Archer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

#[AsController]
#[Route(
    path: '/inscription',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
class RegisterController extends AbstractController
{
    public const ROUTE = 'app_register';

    public function __invoke(
        Request $request,
        EntityManagerInterface $em,
        UserAuthenticatorInterface $authenticator,
        AbstractLoginFormAuthenticator $loginFormAuthenticator
    ): ?Response {
        if ($this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_FULLY)) {
            return $this->redirectToRoute('');
        }

        $archer = new Archer();
        $form = $this->createForm(RegistrationFormType::class, $archer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($archer);
            $em->flush();

            $request->request->set('referer', $this->generateUrl('admin_index'));

            return $authenticator->authenticateUser($archer, $loginFormAuthenticator, $request, [new RememberMeBadge()]);
        }

        return $this->render('app/security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
