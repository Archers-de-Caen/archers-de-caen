<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Security;

use App\Http\Admin\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
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
final class LoginController extends AbstractController
{
    public const string ROUTE = 'admin_login';

    public function __invoke(
        AuthenticationUtils $authenticationUtils,
        AdminUrlGenerator $adminUrlGenerator,
        RouterInterface $router
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token_intention' => 'authenticate',
            'target_path' => $adminUrlGenerator->setDashboard(DashboardController::class)->generateUrl(),
            'devUsers' => [
                [
                    'username' => 'archer@archers-caen.fr',
                    'password' => 'Pwd123',
                    'label' => 'Archer',
                ],
                [
                    'username' => 'editor@archers-caen.fr',
                    'password' => 'Pwd123',
                    'label' => 'Editor',
                ],
                [
                    'username' => 'admin@archers-caen.fr',
                    'password' => 'Pwd123',
                    'label' => 'Admin',
                ],
                [
                    'username' => 'dev@archers-caen.fr',
                    'password' => 'Pwd123',
                    'label' => 'Dev',
                ]
            ],
            'page_title' => 'Archers de Caen - Admin',
        ]);
    }
}
