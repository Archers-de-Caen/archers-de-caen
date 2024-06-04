<?php

declare(strict_types=1);

namespace App\Command\FFTA;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Competition\Manager\CompetitionManager;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Result\Manager\ResultCompetitionManager;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use App\Infrastructure\Service\ArcheryService;
use App\Infrastructure\Service\FFTA\CompetitionResultDTO;
use App\Infrastructure\Service\FFTA\CompetitionResultSearchDTO;
use App\Infrastructure\Service\FFTA\FFTAExtranetService;
use Doctrine\ORM\EntityManagerInterface;

use function Sentry\captureMessage;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:ffta:competition-results-update',
    description: 'Met à jour les résultats depuis le site de la FFTA',
)]
final class FFTACompetitionUpdateCommand extends Command
{
    public const string GET_RESULT_DATE_MIN = '2024-05-13';

    public const string LICENSE_NUMBER_OF_CREATOR_ACTUALITY = '0785039D';

    private SymfonyStyle $io;

    /**
     * @var array{
     *     competition: int,
     *     archer: int,
     *     result: int,
     * }
     */
    private array $report;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CompetitionRepository $competitionRepository,
        private readonly ResultCompetitionRepository $resultCompetitionRepository,
        private readonly ArcherRepository $archerRepository,
        private readonly CompetitionManager $competitionManager,
        private readonly FFTAExtranetService $fftaExtranetService,
        private readonly ResultCompetitionManager $resultCompetitionManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->report = [
            'competition' => 0,
            'archer' => 0,
            'result' => 0,
        ];

        $this->io->info('Run '.$this->getName());
        $this->io->info('Connexion à l\'espace extranet');

        try {
            $competitionResults = $this->getCompetitionResults();
        } catch (HttpExceptionInterface|TransportExceptionInterface|\Exception $httpException) {
            $this->io->error($httpException->getMessage());

            captureMessage($httpException->getMessage());

            return Command::FAILURE;
        }

        foreach ($competitionResults as $competitionCode => $competitionResult) {
            foreach ($competitionResult as $result) {
                $competition = $this->retrieveCompetition($competitionCode, $result);
                $archer = $this->retrieveArcher($result);
                $result = $this->retrieveCompetitionResult($archer, $competition, $result);

                $this->resultCompetitionManager->awardingBadges($result);
                $this->resultCompetitionManager->awardingRecord($result);
            }
        }

        $this->io->info('Rapport:');
        $this->io->table(array_keys($this->report), [$this->report]);

        $this->io->success('finish '.$this->getName());

        return Command::SUCCESS;
    }

    /**
     * @return array<string, array<CompetitionResultDTO>>
     *
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    private function getCompetitionResults(): array
    {
        $this->fftaExtranetService->connect();

        $this->io->info('Récupération des compétitions');

        $season = ArcheryService::getCurrentSeason();
        $minDateStart = \DateTime::createFromFormat('Y-m-d', self::GET_RESULT_DATE_MIN);
        $dateStart = \DateTime::createFromFormat('Y-m-d', ($season - 1).'-01-01');

        if (false === $minDateStart || false === $dateStart) {
            throw new \Exception('Erreur lors de la création de la date');
        }

        $search = new CompetitionResultSearchDTO(
            season: $season,
            dateStart: max($dateStart, $minDateStart)
        );

        $competitionResults = $this->fftaExtranetService->getCompetitionResults($search);

        $this->io->info(\count($competitionResults).' résultat récupéré');

        return $competitionResults;
    }

    private function retrieveCompetition(string $competitionCode, CompetitionResultDTO $result): Competition
    {
        $competition = $this->competitionRepository->findOneBy([
            'fftaCode' => $competitionCode,
        ]);

        if (null === $competition) {
            $competition = (new Competition())
                ->setFftaCode($competitionCode)
                ->setDateStart($result->getStartCompetitionDate())
                ->setDateEnd($result->getEndCompetitionDate())
                ->setLocation($result->getLocation())
                ->setType($result->getDiscipline())
            ;

            $this->em->persist($competition);
            $this->em->flush();

            $creator = $this->archerRepository->findOneBy([
                'licenseNumber' => self::LICENSE_NUMBER_OF_CREATOR_ACTUALITY,
            ]);

            if ($creator) {
                $actuality = $this->competitionManager->createActuality($competition);

                $actuality->setCreatedBy($creator);

                $this->em->persist($actuality);
                $this->em->flush();
            }

            $this->io->info('Création de la compétition '.$competitionCode);

            ++$this->report['competition'];
        }

        return $competition;
    }

    private function retrieveArcher(CompetitionResultDTO $result): Archer
    {
        $archer = $this->archerRepository->findOneBy([
            'licenseNumber' => $result->getLicenseNumber(),
        ]);

        if (null === $archer) {
            $archer = (new Archer())
                ->setLicenseNumber($result->getLicenseNumber())
                ->setFirstname($result->getFirstname())
                ->setLastname($result->getLastname())
            ;

            $this->em->persist($archer);
            $this->em->flush();

            $this->io->info('Création de l\'archer '.$result->getLicenseNumber());

            ++$this->report['archer'];
        }

        return $archer;
    }

    private function retrieveCompetitionResult(
        Archer $archer,
        Competition $competition,
        CompetitionResultDTO $result
    ): ResultCompetition {
        $competitionResult = $this->resultCompetitionRepository->findOneBy([
            'archer' => $archer,
            'competition' => $competition,
            'departureNumber' => $result->getStartNumber(),
            'weapon' => $result->getWeapon(),
            'category' => $result->getCategory(),
        ]);

        if (null === $competitionResult) {
            $competitionResult = new ResultCompetition();
            $competitionResult
                ->setDepartureNumber($result->getStartNumber())
                ->setCompetition($competition)
                ->setArcher($archer)
                ->setRank(1 === $result->getStartNumber() ? $result->getQualificationPlace() : null)
                ->setScore($result->getScore())
                ->setWeapon($result->getWeapon())
                ->setCategory($result->getCategory())
                ->setCompletionDate($result->getCompletionDate())
            ;

            $this->em->persist($competitionResult);
            $this->em->flush();

            $this->io->info('Création du résultat de la compétition '.$competition->getFftaCode()." pour l'archer ".$result->getLicenseNumber());

            ++$this->report['result'];
        }

        return $competitionResult;
    }
}
