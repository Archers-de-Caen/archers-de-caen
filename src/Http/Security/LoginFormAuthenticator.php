<?php

declare(strict_types=1);

namespace App\Http\Security;

use App\Http\App\Controller\Security\LoginController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return LoginController::ROUTE === $request->attributes->get('_route')
            && $request->isMethod(Request::METHOD_POST)
        ;
    }

    public function authenticate(Request $request): Passport
    {
        /** @var ?string $password */
        $password = $request->request->get('password');

        /** @var ?string $username */
        $username = $request->request->get('username');

        /** @var ?string $csrfToken */
        $csrfToken = $request->request->get('csrf_token');

        if (empty($password) || empty($username) || empty($csrfToken)) {
            throw new AuthenticationException('Missing credentials');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('login', $csrfToken),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}
