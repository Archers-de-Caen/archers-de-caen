<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    public const ROUTE_ADMIN_LOGIN = 'admin_login';

    #[Route('/connexion', name: self::ROUTE_ADMIN_LOGIN)]
    public function login(): Response
    {
        return $this->redirectToRoute(\App\Http\App\Controller\Security\LoginController::ROUTE_APP_LOGIN);
    }
}
