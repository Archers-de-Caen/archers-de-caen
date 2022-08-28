<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use App\Domain\Archer\Form\RegistrationFormType;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Auth\Mailer\SecurityMailer;
use App\Domain\Auth\Manager\AuthTokenManager;
use App\Domain\Auth\Subscriber\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class SecurityController extends AbstractController
{
    public const ROUTE_APP_LOGIN = 'app_login';
    public const ROUTE_APP_REGISTER = 'app_register';
    public const ROUTE_APP_LOGOUT = 'app_logout';
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

            $error = new AuthenticationException('Identifiant introuvable !');
        }

        return $this->render('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/connexion/mot-de-passe', name: self::ROUTE_APP_LOGIN_PASSWORD, methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function loginPassword(Request $request, AuthenticationUtils $authenticationUtils): Response
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

        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var array $formData */
            $formData = $request->request->all()['registration_form'];
            $licenseNumber = (string) $formData['licenseNumber'];

            $archer = $em->getRepository(Archer::class)->findOneBy(['licenseNumber' => $licenseNumber]) ?? new Archer();
        }

        $form = $this->createForm(RegistrationFormType::class, $archer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($archer);
            $em->flush();

            $request->request->set('referer', $request->query->get('referer') ?: $this->generateUrl(DefaultController::ROUTE_APP_INDEX));

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
