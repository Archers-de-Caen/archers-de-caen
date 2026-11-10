<?php

declare(strict_types=1);

namespace App\Command\FFTA;

use App\Infrastructure\Service\ArcheryService;
use App\Infrastructure\Service\FFTA\CompetitionResultDTO;
use App\Infrastructure\Service\FFTA\CompetitionResultImporter;
use App\Infrastructure\Service\FFTA\CompetitionResultSearchDTO;
use App\Infrastructure\Service\FFTA\FFTAExtranetService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Met à jour les résultats depuis le site de la FFTA',
)]
final readonly class FFTACompetitionUpdateCommand
{
    public const string COMMAND_NAME = 'app:ffta:competition-results-update';

    public const string GET_RESULT_DATE_MIN = '2025-09-01';

    public function __construct(
        private FFTAExtranetService $fftaExtranetService,
        private CompetitionResultImporter $competitionResultImporter,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(SymfonyStyle $io): int
    {
        $io->info('Run '.self::COMMAND_NAME);
        $io->info("Connexion à l'espace extranet");

        try {
            $competitionResults = $this->getCompetitionResults($io);
        } catch (HttpExceptionInterface|TransportExceptionInterface|\Exception $httpException) {
            $io->error($httpException->getMessage());

            return Command::FAILURE;
        }

        $report = $this->competitionResultImporter->import(
            $competitionResults,
            static function (string $message) use ($io): void {
                $io->info($message);
            }
        );

        $io->info('Rapport:');
        $io->table(array_keys($report), [array_map(static fn (array $items): int => \count($items), $report)]);

        $io->success('finish '.self::COMMAND_NAME);

        return Command::SUCCESS;
    }

    /**
     * @return array<string, array<CompetitionResultDTO>>
     *
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    private function getCompetitionResults(SymfonyStyle $io): array
    {
        $this->fftaExtranetService->connect();

        $io->info('Récupération des compétitions');

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

        $io->info(\count($competitionResults).' résultat récupéré');

        return $competitionResults;
    }
}
