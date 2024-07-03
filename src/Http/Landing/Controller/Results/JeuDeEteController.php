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
use App\Http\Landing\Form\SendResultForJeuDeLEteCompetitionForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class JeuDeEteController extends AbstractController
{
    private const string COMPETITION_SLUG = 'jeu-de-ete';

    public const string ROUTE_RESULT = 'landing_jeu_de_l_ete_result';

    public const string ROUTE_SUBMIT_RESULT = 'landing_jeu_de_l_ete_submit_result';

    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly ResultCompetitionRepository $resultCompetitionRepository,
        private readonly CompetitionService $competitionService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route(
        path: '/jeu-de-l-ete',
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
                ->setDateStart(new \DateTimeImmutable('2024-07-01'))
                ->setDateEnd(new \DateTimeImmutable('2024-08-31'))
                ->setLocation('Archers de Caen');

            $this->competitionRepository->save($competition, true);
        }

        $groupedResults = $this->competitionService->groupCompetitionResultsByWeaponAndCategories($competition);

        return $this->render('/landing/results/jeu-de-l-ete.html.twig', [
            'competition' => $competition,
            'results' => $groupedResults['results'],
            'participantCount' => \count($groupedResults['participants']),
            'recordCount' => $groupedResults['recordCount'],
            'podiumCount' => $groupedResults['podiumCount'],
        ]);
    }

    #[Route(
        path: '/jeu-de-l-ete/envoyer-resultat',
        name: self::ROUTE_SUBMIT_RESULT,
        methods: [
            Request::METHOD_GET,
            Request::METHOD_POST,
        ]
    )]
    public function submit(
        Request $request,
        #[MapQueryParameter('password')]
        string $userPassword,
        #[Autowire(env: 'APP_PASSWORD_JEU_DE_ETE')]
        string $password
    ): Response {
        if ($userPassword !== $password) {
            $this->addFlash('danger', 'Vous n\'avez pas le droit d\'accéder à cette page.');

            return $this->redirectToRoute(self::ROUTE_RESULT);
        }

        $form = $this->createForm(SendResultForJeuDeLEteCompetitionForm::class);
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

        return $this->render('/landing/results/jeu-de-l-ete-send-results.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
