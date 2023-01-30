<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Security;

use App\Http\Admin\Controller\DashboardController;
use App\Http\App\Controller\SecurityController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    public const ROUTE_ADMIN_LOGIN = 'admin_login';

    #[Route('/connexion', name: self::ROUTE_ADMIN_LOGIN)]
    public function login(
        AuthenticationUtils $authenticationUtils,
        AdminUrlGenerator $adminUrlGenerator,
        RouterInterface $router
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'action' => $router->generate(SecurityController::ROUTE_APP_LOGIN),
            'target_path' => $adminUrlGenerator->setDashboard(DashboardController::class)->generateUrl(),
        ]);
    }
}
