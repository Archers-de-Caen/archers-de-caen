<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Trapta;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

// le .php est dû au fonctionnement de TraptaCloud
#[AsController]
#[Route(
    path: '/trapta/updatepositions.php',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: [Request::METHOD_GET, Request::METHOD_POST]
)]
class UpdatePositionsController extends AbstractController
{
    public const ROUTE = 'landing_trapta_update_position';

    public function __invoke(): Response
    {
        return $this->render('/landing/trapta/index.html.twig');
    }
}
