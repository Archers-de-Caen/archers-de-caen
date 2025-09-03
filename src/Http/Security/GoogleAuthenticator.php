<?php

declare(strict_types=1);

namespace App\Http\Security;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Http\App\Controller\Security\OAuth2Controller;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private string $authType;

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $em,
        private readonly ArcherRepository $archerRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        try {
            $this->authType = $this->getAuthTypeFromRequest($request);
        } catch (\RuntimeException) {
            return false;
        }

        return OAuth2Controller::ROUTE_CHECK === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient($this->authType);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $authUser */
                $authUser = $client->fetchUserFromToken($accessToken);

                $email = $authUser->getEmail();
                $hostedDomain = $authUser->getHostedDomain();

                /** @var string $authId */
                $authId = $authUser->getId();

                // have they logged in with Google before? Easy!
                $existingUser = $this->archerRepository->findOneBy([
                    'externalAuthId' => $authId,
                    'externalAuthType' => $this->authType,
                ]);

                $existingUserWithEmail = $this->archerRepository->findOneBy([
                    'email' => $email,
                ]);

                // User doesn't exist, we create it !
                if (!$existingUser && !$existingUserWithEmail) {
                    $existingUser = (new Archer())
                        ->setEmail($email)
                        ->setExternalAuthId($authId)
                        ->setExternalAuthType($this->authType)
                        ->setHostedDomain($hostedDomain)
                        ->setFirstName($authUser->getFirstName())
                        ->setLastName($authUser->getLastName())
                    ;

                    $this->em->persist($existingUser);
                } elseif (!$existingUser) {
                    $existingUser = $existingUserWithEmail;
                    $existingUser
                        ->setExternalAuthId($authId)
                        ->setExternalAuthType($this->authType)
                        ->setHostedDomain($hostedDomain)
                    ;
                }

                $this->em->flush();

                return $existingUser;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    private function getAuthTypeFromRequest(Request $request): string
    {
        $referer = $request->headers->get('referer');

        if (!$referer) {
            throw new \RuntimeException('Missing referer');
        }

        if (str_starts_with($referer, 'https://accounts.google.com/')) {
            return 'google';
        }

        throw new \RuntimeException('Unknown auth type');
    }
}
