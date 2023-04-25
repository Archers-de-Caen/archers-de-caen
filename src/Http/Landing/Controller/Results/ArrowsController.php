<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Badge\Repository\BadgeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats/fleche-de-progression',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class ArrowsController extends AbstractController
{
    public const ROUTE = 'landing_results_arrow';

    public function __invoke(ArcherRepository $archerRepository, BadgeRepository $badgeRepository): Response
    {
        $archers = $archerRepository->findAll();

        $archers = array_filter($archers, static fn (Archer $archer) => $archer->getResultsProgressArrow()->count());

        usort(
            $archers,
            static function (Archer $one, Archer $two): int {
                $levelOne = $one->getBestProgressArrowObtained()?->getBadge()?->getLevel();
                $levelTwo = $two->getBestProgressArrowObtained()?->getBadge()?->getLevel();

                return $levelOne > $levelTwo ? -1 : 1;
            }
        );

        return $this->render('/landing/results/result-progress-arrow.html.twig', [
            'archers' => $archers,
            'progressArrows' => $badgeRepository->findProgressArrow(),
        ]);
    }
}
