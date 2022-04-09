<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/debug', name: 'app_debug')]
    public function debug(EntityManagerInterface $em): Response
    {
        /** @var Archer $user */
        $user = $this->getUser();

        $user->addRole(Archer::ROLE_DEVELOPER);

        $em->flush();

        return $this->redirectToRoute('admin_index');
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $em, UserAuthenticatorInterface $authenticator, AbstractLoginFormAuthenticator $loginFormAuthenticator): ?Response
    {
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

    /**
     * @throws Exception
     */
    #[Route('/deconnexion', name: 'app_logout', methods: 'GET')]
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }
}
