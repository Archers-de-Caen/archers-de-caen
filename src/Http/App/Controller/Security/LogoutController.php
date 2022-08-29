<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    public const ROUTE_APP_LOGOUT = 'app_logout';

    /**
     * Handle by security.yaml
     */
    #[Route('/deconnexion', name: self::ROUTE_APP_LOGOUT, methods: Request::METHOD_GET)]
    public function logout(): void {}
}
