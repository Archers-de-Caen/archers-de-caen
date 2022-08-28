<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use App\Domain\Archer\Form\RegistrationFormType;
use App\Domain\Archer\Model\Archer;
use App\Infrastructure\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class SecurityController extends AbstractController
{
    public const ROUTE_APP_LOGIN = 'app_login';
    public const ROUTE_APP_REGISTER = 'app_register';
    public const ROUTE_APP_LOGOUT = 'app_logout';

    #[Route('/connexion', name: self::ROUTE_APP_LOGIN, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/inscription', name: self::ROUTE_APP_REGISTER, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserAuthenticatorInterface $authenticator,
        LoginFormAuthenticator $loginFormAuthenticator
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

            $request->request->set('referer', $request->query->get('referer') ?: $this->generateUrl('admin_index'));

            return $authenticator->authenticateUser($archer, $loginFormAuthenticator, $request, [new RememberMeBadge()]);
        }

        return $this->render('app/security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handle by security.yaml
     */
    #[Route('/deconnexion', name: self::ROUTE_APP_LOGOUT, methods: Request::METHOD_GET)]
    public function logout(): void {}
}
