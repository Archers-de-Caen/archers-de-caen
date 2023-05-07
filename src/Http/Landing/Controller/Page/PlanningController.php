<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/planning',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
class PlanningController extends AbstractController
{
    public const ROUTE = 'landing_planning';

    public function __invoke(Request $request): Response
    {
        if ($request->query->has('iframe')) {
            return $this->render('/landing/club/planning-iframe.html.twig');
        }

        return $this->render('/landing/club/planning.html.twig');
    }
}
