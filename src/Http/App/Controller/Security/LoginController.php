<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Auth\Mailer\SecurityMailer;
use App\Domain\Auth\Manager\AuthTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public const ROUTE_APP_LOGIN = 'app_login';
    public const ROUTE_APP_CONNECTION_LINK = 'app_connection_link';
    public const ROUTE_APP_LOGIN_PASSWORD = 'app_login_password';

    #[Route('/connexion', name: self::ROUTE_APP_LOGIN, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        ArcherRepository $archerRepository,
        SecurityMailer $mailer,
        AuthTokenManager $authTokenManager,
        EntityManagerInterface $em
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var string $identifier */
            $identifier = $request->request->get('identifier');

            $request->getSession()->set(Security::LAST_USERNAME, $identifier);

            $archer = $archerRepository->loadUserByIdentifier($identifier);

            if ($archer) {
                if ($archer->getPassword()) {
                    return $this->redirectToRoute(self::ROUTE_APP_LOGIN_PASSWORD);
                }

                $authTokenManager->create($archer);

                $em->flush();

                $mailer->sendConnectionLink($archer);

                return $this->redirectToRoute(self::ROUTE_APP_CONNECTION_LINK);
            }

            $error = new AuthenticationException('Identifiants introuvables !');
        }

        return $this->render('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/connexion/mot-de-passe', name: self::ROUTE_APP_LOGIN_PASSWORD, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function loginPassword(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('app/security/login-password.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/connexion/lien-de-connexion', name: self::ROUTE_APP_CONNECTION_LINK, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function connectionLink(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('app/security/login-connection-link.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
