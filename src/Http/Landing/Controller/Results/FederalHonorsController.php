<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Config\Weapon;
use App\Domain\Badge\Model\Badge;
use App\Domain\Badge\Repository\BadgeRepository;
use App\Domain\Competition\Config\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats/distinctions-federales',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class FederalHonorsController extends AbstractController
{
    public const ROUTE = 'landing_results_federal_honors';

    public function __invoke(BadgeRepository $badgeRepository): Response
    {
        $badges = $badgeRepository->findBy(['type' => Badge::COMPETITION]);

        return $this->render('/landing/results/result-badges.html.twig', [
            'badges' => $badges,
            'weapons' => Weapon::getInOrder(),
            'competitionTypes' => array_filter(
                Type::getInOrder(),
                static function (Type $competitionType) use ($badges) {
                    foreach ($badges as $badge) {
                        if (
                            isset($badge->getConditions()['weapon']) &&
                            $badge->getCompetitionType() === $competitionType
                        ) {
                            return true;
                        }
                    }

                    return false;
                }
            ),
        ]);
    }
}
