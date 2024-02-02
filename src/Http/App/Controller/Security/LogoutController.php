<?php

declare(strict_types=1);

namespace App\Http\App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/deconnexion',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class LogoutController extends AbstractController
{
    public const ROUTE = 'app_logout';

    /**
     * @throws \Exception
     */
    public function __invoke(): void
    {
        // controller can be blank: it will never be called!
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }
}
