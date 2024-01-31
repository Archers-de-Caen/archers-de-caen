<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[AsController]
#[Route(
    path: '/connexion',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
class LoginController extends AbstractController
{
    public const ROUTE = 'app_login';

    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
