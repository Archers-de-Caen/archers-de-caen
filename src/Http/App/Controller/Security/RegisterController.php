<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use App\Domain\Archer\Form\RegistrationFormType;
use App\Domain\Archer\Model\Archer;
use App\Domain\Auth\Subscriber\LoginFormAuthenticator;
use App\Http\App\Controller\DefaultController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class RegisterController extends AbstractController
{
    public const ROUTE_APP_REGISTER = 'app_register';

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
}
