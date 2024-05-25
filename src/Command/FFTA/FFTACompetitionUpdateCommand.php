<?php

declare(strict_types=1);

namespace App\Command\FFTA;

use App\Infrastructure\Service\ArcheryService;
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
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly FFTAExtranetService $fftaExtranetService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Run '.$this->getName());

        $season = ArcheryService::getCurrentSeason();

        $io->info('Connexion à l\'espace extranet');
        try {
            $this->fftaExtranetService->connect();

            $io->info('Récupération des compétitions');

            $competitionResults = $this->fftaExtranetService->getCompetitionResults($season);

            $io->info(\count($competitionResults).' résultat récupéré');
        } catch (HttpExceptionInterface|TransportExceptionInterface|\Exception $httpException) {
            $io->error($httpException->getMessage());

            captureMessage($httpException->getMessage());

            return Command::FAILURE;
        }

        $io->info('Flush');

        $this->em->flush();

        $io->success('finish '.$this->getName());

        return Command::SUCCESS;
    }
}
