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
    path: '/trapta/competition/{eventName}/position',
    name: self::ROUTE,
    methods: [Request::METHOD_GET]
)]
class PositionController extends AbstractController
{
    public const ROUTE = 'landing_trapta_position';

    public function __invoke(string $eventName): Response
    {
        return $this->render('/landing/trapta/position.html.twig');
    }
}
