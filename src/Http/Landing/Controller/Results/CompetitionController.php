<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Service\CompetitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/resultats/concours/{slug}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class CompetitionController extends AbstractController
{
    public const string ROUTE = 'landing_results_competition';

    public function __construct(
        private readonly CompetitionService $competitionService
    ) {
    }

    public function __invoke(Request $request, Competition $competition): Response
    {
        $groupedResults = $this->competitionService->groupCompetitionResultsByWeaponAndCategories($competition);

        $template = '/landing/results/competitions/results-competition.html.twig';
        if ($request->query->get('iframe')) {
            $template = '/landing/results/competitions/results-competition-iframe.html.twig';
        }

        return $this->render($template, [
            'competition' => $competition,
            'results' => $groupedResults['results'],
            'participantCount' => \count($groupedResults['participants']),
            'recordCount' => $groupedResults['recordCount'],
            'podiumCount' => $groupedResults['podiumCount'],
        ]);
    }
}
