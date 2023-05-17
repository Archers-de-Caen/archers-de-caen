<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Trapta;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/trapta',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: [Request::METHOD_GET, Request::METHOD_POST]
)]
class IndexController extends AbstractController
{
    public const ROUTE = 'landing_trapta_index';

    public function __invoke(): Response
    {
        return $this->render('/landing/trapta/index.html.twig');
    }
}
