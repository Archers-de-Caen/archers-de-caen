<?php

declare(strict_types=1);

namespace App\Command\Emails;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\GalleryRepository;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Infrastructure\Mailing\MonthlyReportNewsletterMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:emails:monthly-report',
    description: 'Envoie un rapport mensuel par email.',
)]
class MonthlyReportCommand extends Command
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly CompetitionRepository $competitionRepository,
        private readonly GalleryRepository $galleryRepository,
        private readonly MessageBusInterface $messageBus,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Run '.$this->getName());

        $actualities = $this->pageRepository->findLastMonthActualities();
        $galleries = $this->galleryRepository->findLastMonthGalleries();
        $competitions = $this->competitionRepository->findLastMonthCompetitions();

        $message = new MonthlyReportNewsletterMessage(
            actualityUuids: array_map(function (Page $actuality) {
                /** @var Uuid $id */
                $id = $actuality->getId();

                return $id;
            }, $actualities),
            galleryUuids: array_map(function (Gallery $gallery) {
                /** @var Uuid $id */
                $id = $gallery->getId();

                return $id;
            }, $galleries),
            competitionUuids: array_map(function (Competition $competition) {
                /** @var Uuid $id */
                $id = $competition->getId();

                return $id;
            }, $competitions),
        );

        $this->messageBus->dispatch($message);

        $io->success('Le rapport mensuel a été envoyé avec succès.');

        return Command::SUCCESS;
    }
}
