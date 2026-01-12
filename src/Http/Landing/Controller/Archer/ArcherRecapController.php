<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Archer;

use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Repository\BadgeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/archer/{licenseNumber}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class ArcherRecapController extends AbstractController
{
    public const string ROUTE = 'landing_archer';

    public function __construct(
        private readonly BadgeRepository $badgeRepository,
    ) {
    }

    public function __invoke(Archer $archer): Response
    {
        return $this->render('/landing/archers/archer.html.twig', [
            'archer' => $archer,
            'progressArrows' => $this->badgeRepository->findProgressArrow(),
        ]);
    }
}
