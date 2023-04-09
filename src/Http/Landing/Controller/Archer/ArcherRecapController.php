<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Archer;

use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Repository\BadgeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArcherRecapController extends AbstractController
{
    public const ROUTE_LANDING_ARCHER = 'landing_archer';

    #[Route('/archer/{licenseNumber}', name: self::ROUTE_LANDING_ARCHER)]
    public function __invoke(Archer $archer, BadgeRepository $badgeRepository): Response
    {
        return $this->render('/landing/archers/archer.html.twig', [
            'archer' => $archer,
            'progressArrows' => $badgeRepository->findProgressArrow(),
        ]);
    }
}
