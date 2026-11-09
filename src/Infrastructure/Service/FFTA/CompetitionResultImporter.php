<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Competition\Service\CompetitionService;
use App\Domain\Newsletter\NewsletterType;
use App\Domain\Result\Manager\ResultCompetitionManager;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use App\Infrastructure\Mailing\AdminNotificationMessage;
use App\Infrastructure\Mailing\CompetitionResultsNewsletterMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CompetitionResultImporter
{
    public const string LICENSE_NUMBER_OF_CREATOR_ACTUALITY = '0785039D';

    public function __construct(
        private EntityManagerInterface $em,
        private CompetitionRepository $competitionRepository,
        private ResultCompetitionRepository $resultCompetitionRepository,
        private ArcherRepository $archerRepository,
        private CompetitionService $competitionManager,
        private ResultCompetitionManager $resultCompetitionManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param array<string, array<CompetitionResultDTO>> $competitionResults résultats groupés par code de compétition
     * @param (callable(string): void)|null              $log                callback de log (ex: sortie console)
     *
     * @return array{competition: array<Competition>, archer: array<Archer>, result: array<ResultCompetition>}
     *
     * @throws ExceptionInterface
     */
    public function import(array $competitionResults, ?callable $log = null): array
    {
        $log ??= static function (string $message): void {
        };

        $report = [
            'competition' => [],
            'archer' => [],
            'result' => [],
        ];

        foreach ($competitionResults as $competitionCode => $competitionResult) {
            foreach ($competitionResult as $result) {
                $competition = $this->retrieveCompetition((string) $competitionCode, $result, $report, $log);
                $archer = $this->retrieveArcher($result, $report, $log);
                $resultCompetition = $this->retrieveCompetitionResult($archer, $competition, $result, $report, $log);

                $this->resultCompetitionManager->awardingBadges($resultCompetition);
                $this->resultCompetitionManager->awardingRecord($resultCompetition);
            }
        }

        if ([] !== $report['result']) {
            $this->messageBus->dispatch(new AdminNotificationMessage(
                'Les résultats des compétitions ont été importés',
                'mails/admin/new-competition-imported.html.twig',
                [
                    'report' => $report,
                ]
            ));

            $log('Notification envoyée');
        }

        foreach ($report['competition'] as $competition) {
            $this->messageBus->dispatch(new CompetitionResultsNewsletterMessage(
                $competition->getId(),
                NewsletterType::COMPETITION_RESULTS_NEW
            ));
        }

        /* @phpstan-ignore-next-line return.type */
        return $report;
    }

    private function retrieveCompetition(string $competitionCode, CompetitionResultDTO $result, array &$report, callable $log): Competition
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

            $log('Création de la compétition '.$competitionCode);

            $report['competition'][] = $competition;
        }

        return $competition;
    }

    private function retrieveArcher(CompetitionResultDTO $result, array &$report, callable $log): Archer
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

            $log('Création de l\'archer '.$result->getLicenseNumber());

            $report['archer'][] = $archer;
        }

        return $archer;
    }

    private function retrieveCompetitionResult(
        Archer $archer,
        Competition $competition,
        CompetitionResultDTO $result,
        array &$report,
        callable $log,
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

            $log('Création du résultat de la compétition '.$competition->getFftaCode()." pour l'archer ".$result->getLicenseNumber());

            $report['result'][] = $competitionResult;
        }

        return $competitionResult;
    }
}
