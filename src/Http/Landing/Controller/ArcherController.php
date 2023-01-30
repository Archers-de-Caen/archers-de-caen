<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Repository\BadgeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ArcherController extends AbstractController
{
    public const ROUTE_LANDING_ARCHER = 'landing_archer';

    #[Route('/archer/{licenseNumber}', name: self::ROUTE_LANDING_ARCHER)]
    public function page(Archer $archer, BadgeRepository $badgeRepository): Response
    {
        return $this->render('/landing/archers/archer.html.twig', [
            'archer' => $archer,
            'progressArrows' => $badgeRepository->findProgressArrow(),
        ]);
    }
}
