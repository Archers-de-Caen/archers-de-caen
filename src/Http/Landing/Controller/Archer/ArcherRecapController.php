<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Archer;

use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Repository\BadgeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/archer/{licenseNumber}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class ArcherRecapController extends AbstractController
{
    public const ROUTE = 'landing_archer';

    public function __invoke(Archer $archer, BadgeRepository $badgeRepository): Response
    {
        return $this->render('/landing/archers/archer.html.twig', [
            'archer' => $archer,
            'progressArrows' => $badgeRepository->findProgressArrow(),
        ]);
    }
}
