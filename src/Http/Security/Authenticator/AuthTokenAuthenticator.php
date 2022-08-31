<?php

declare(strict_types=1);

namespace App\Http\Security\Authenticator;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Auth\Manager\AuthTokenManager;
use App\Http\App\Controller\DefaultController;
use App\Http\App\Controller\Security\LoginController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see https://symfony.com/doc/current/security/authenticator_manager.html#passport-badges
 * @see https://blog.yousign.io/posts/les-nouveautes-dans-le-composant-securite-de-symfony
 */
class AuthTokenAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        readonly private RouterInterface $router,
        readonly private ArcherRepository $archerRepository,
        readonly private EntityManagerInterface $em,
        readonly private AuthTokenManager $authTokenManager,
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): bool
    {
        $identifier = $request->query->get('identifier') ?? $request->request->get('identifier');
        $code = $request->query->get('code') ?? $request->request->get('code');

        return LoginController::ROUTE_APP_CONNECTION_LINK === $request->attributes->get('_route') && $identifier && $code;
    }

    public function authenticate(Request $request): Passport
    {
        $request->getSession()->set(Security::LAST_USERNAME, $request->request->get('identifier'));

        $identifier = (string) ($request->query->get('identifier') ?? $request->request->get('identifier'));
        $code = (string) ($request->query->get('code') ?? $request->request->get('code'));

        return new Passport(
            new UserBadge($identifier, function ($userIdentifier) {
                return $this->archerRepository->loadUserByIdentifier($userIdentifier);
            }),
            new CustomCredentials(
                function ($credentials, Archer $user) {
                    foreach ($user->getActiveAuthTokens() as $authToken) {
                        if ($this->authTokenManager->verifyToken($authToken, $credentials)) {
                            $authToken->useAuthToken();

                            $this->em->flush();

                            return true;
                        }
                    }

                    return false;
                }, $code
            ),
            [
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
        return $this->router->generate(LoginController::ROUTE_APP_CONNECTION_LINK);
    }
}
