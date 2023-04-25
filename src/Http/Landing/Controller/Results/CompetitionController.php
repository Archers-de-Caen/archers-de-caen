<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Competition\Model\Competition;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats/concours/{slug}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class CompetitionController extends AbstractController
{
    public const ROUTE = 'landing_results_competition';

    public function __invoke(Request $request, Competition $competition): Response
    {
        $results = [];
        $participants = [];
        $recordCount = 0;
        $podiumCount = 0;

        foreach (Weapon::cases() as $weapon) {
            foreach (Category::cases() as $category) {
                foreach ($competition->getResults() as $result) {
                    if (
                        $category->value === $result->getCategory()?->value &&
                        $weapon->value === $result->getWeapon()?->value
                    ) {
                        if (!isset($results[$weapon->value])) {
                            $results[$weapon->value] = [];
                        }

                        if (!isset($results[$weapon->value][$category->value])) {
                            $results[$weapon->value][$category->value] = [];
                        }

                        if (($archer = $result->getArcher()) && !\in_array($archer, $participants, true)) {
                            $participants[] = $archer;
                        }

                        if ($result->getRecord()) {
                            ++$recordCount;
                        }

                        if ($result->getRank() <= 3) {
                            ++$podiumCount;
                        }

                        $results[$weapon->value][$category->value][] = $result;
                    }
                }
            }
        }

        $template = '/landing/results/competitions/results-competition.html.twig';
        if ($request->query->get('iframe')) {
            $template = '/landing/results/competitions/results-competition-iframe.html.twig';
        }

        return $this->render($template, [
            'competition' => $competition,
            'results' => $results,
            'participantCount' => \count($participants),
            'recordCount' => $recordCount,
            'podiumCount' => $podiumCount,
        ]);
    }
}
