<?php

declare(strict_types=1);

namespace App\Domain\Auth\Subscriber;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Http\App\Controller\DefaultController;
use App\Http\App\Controller\RegisterController;
use App\Http\App\Controller\Security\LoginController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see https://symfony.com/doc/current/security/authenticator_manager.html#passport-badges
 * @see https://blog.yousign.io/posts/les-nouveautes-dans-le-composant-securite-de-symfony
 */
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        readonly private RouterInterface $router,
        readonly private UserPasswordHasherInterface $passwordEncoder,
        readonly private ArcherRepository $archerRepository,
        readonly private EntityManagerInterface $em
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): bool
    {
        return LoginController::ROUTE_APP_LOGIN_PASSWORD === $request->attributes->get('_route') && $request->isMethod(Request::METHOD_POST);
    }

    public function authenticate(Request $request): Passport
    {
        $request->getSession()->set(Security::LAST_USERNAME, $request->request->get('identifier'));

        $identifier = (string) $request->request->get('identifier');
        $password = (string) $request->request->get('password');
        $csrfToken = (string) $request->request->get('csrf');

        return new Passport(
            new UserBadge($identifier, function ($userIdentifier) {
                // optionally pass a callback to load the User manually
                return $this->archerRepository->loadUserByIdentifier($userIdentifier);
            }),
            new CustomCredentials(
                // If this function returns anything else than `true`, the credentials
                // are marked as invalid.
                // The $credentials parameter is equal to the next argument of this class
                fn ($credentials, Archer $user) => $this->passwordEncoder->isPasswordValid($user, $credentials),
                $password
            ),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        /** @var Archer $user */
        $user = $token->getUser();

        $user->updateLastLogin();

        $this->em->flush();

        if ($referer = (string) $request->request->get('referer')) {
            return new RedirectResponse($referer);
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return  new RedirectResponse($targetPath);
        }

        return  new RedirectResponse($this->router->generate(DefaultController::ROUTE_APP_INDEX));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->getLoginUrl($request));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate(LoginController::ROUTE_APP_LOGIN);
    }
}
