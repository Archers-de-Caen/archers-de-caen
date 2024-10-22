<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Competition\Service\CompetitionService;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use App\Http\Landing\Form\SendResultForInternalCompetitionForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class InternalCompetitionController extends AbstractController
{
    private const string COMPETITION_SLUG = 'concours-interne';

    public const string ROUTE_RESULT = 'landing_internal_competition_result';

    public const string PATH_RESULT = '/concours-interne';

    public const string ROUTE_SUBMIT_RESULT = 'landing_internal_competition_submit_result';

    public const string PATH_SUBMIT_RESULT = '/concours-interne/envoyer-resultat';

    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly ResultCompetitionRepository $resultCompetitionRepository,
        private readonly CompetitionService $competitionService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route(
        path: self::PATH_RESULT,
        name: self::ROUTE_RESULT,
        methods: [
            Request::METHOD_GET,
        ]
    )]
    public function results(): Response
    {
        $competition = $this->competitionRepository->findOneBy(['slug' => self::COMPETITION_SLUG]);

        if (!$competition instanceof Competition) {
            $competition = new Competition();
            $competition
                ->setSlug(self::COMPETITION_SLUG)
                ->setType(Type::HOBBIES)
                ->setDateStart(new \DateTimeImmutable('2024-10-23'))
                ->setDateEnd(new \DateTimeImmutable('2025-02-21'))
                ->setLocation('Archers de Caen');

            $this->competitionRepository->save($competition, true);
        }

        $groupedResults = $this->competitionService->groupCompetitionResultsByWeaponAndCategories($competition);

        return $this->render('/landing/results/internal-competition/result.html.twig', [
            'competition' => $competition,
            'results' => $groupedResults['results'],
            'participantCount' => \count($groupedResults['participants']),
            'recordCount' => $groupedResults['recordCount'],
            'podiumCount' => $groupedResults['podiumCount'],
        ]);
    }

    #[Route(
        path: self::PATH_SUBMIT_RESULT,
        name: self::ROUTE_SUBMIT_RESULT,
        methods: [
            Request::METHOD_GET,
            Request::METHOD_POST,
        ]
    )]
    public function submit(
        Request $request,
    ): Response {
        $form = $this->createForm(SendResultForInternalCompetitionForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $competition = $this->competitionRepository->findOneBy(['slug' => self::COMPETITION_SLUG]);

            if (!$competition instanceof Competition) {
                $this->addFlash('danger', 'La compétition n\'existe pas.');

                return $this->redirectToRoute(self::ROUTE_RESULT);
            }

            /** @var array $data */
            $data = $form->getData();

            $category = match ($data['category']) {
                Gender::MAN => Category::SCRATCH_MAN,
                Gender::WOMAN => Category::SCRATCH_WOMAN,
                default => throw new \InvalidArgumentException('Invalid category'),
            };

            $resultCompetition = new ResultCompetition();
            $resultCompetition
                ->setCompetition($competition)
                ->setArcher($data['archer'])
                ->setWeapon($data['weapon'])
                ->setCategory($category)
                ->setScore($data['score'])
                ->setCompletionDate(\DateTimeImmutable::createFromInterface($data['completionDate']))
                ->setScoreSheet($data['scoreSheet']);

            $this->resultCompetitionRepository->save($resultCompetition, true);

            $this->competitionService->updateAllRanking($competition);

            $this->em->flush();

            $this->addFlash('success', 'Résultat enregistré.');

            return $this->redirectToRoute(self::ROUTE_RESULT);
        }

        return $this->render('/landing/results/internal-competition/send-results.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
