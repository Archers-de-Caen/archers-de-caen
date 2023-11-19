<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class OAuth2Controller extends AbstractController
{
    public const ROUTE_CHECK = 'app_oauth2_check';
    public const ROUTE_GOOGLE = 'app_oauth2_google';

    #[Route('/oauth2/google', name: self::ROUTE_GOOGLE)]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    #[Route(
        path: '/oauth2/check',
        name: self::ROUTE_CHECK,
        methods: [
            Request::METHOD_GET,
            Request::METHOD_POST,
        ]
    )]
    public function check(): void
    {
        throw new \RuntimeException('Guard authenticator');
    }
}
