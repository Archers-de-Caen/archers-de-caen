<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class ResultsController extends AbstractController
{
    public const ROUTE = 'landing_results';

    public function __invoke(): Response
    {
        return $this->render('/landing/results/results.html.twig');
    }
}
