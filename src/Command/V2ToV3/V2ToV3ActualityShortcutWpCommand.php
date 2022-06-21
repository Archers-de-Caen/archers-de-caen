<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use App\Domain\Cms\Model\Page;
use App\Domain\Competition\Model\Competition;
use App\Http\Landing\Controller\CompetitionController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:v2-to-v3:wp-posts-shortcut',
    description: 'Migration des actualités de la version 2 du site vers la 3',
)]
class V2ToV3ActualityShortcutWpCommand extends Command
{
    use DownloadTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $posts = $this->em->getRepository(Page::class)->findAll();
        $competitions = $this->reformatCompetitionsArray($this->em->getRepository(Competition::class)->findAll());

        $io->progressStart(count($posts));

        foreach ($posts as $post) {
            $io->progressAdvance();

            $content = $post->getContent();

            if ($content) {
                $this->replaceCompetitionShortcut($content, '/\[concours id=(\d{9})\]/m', $competitions);
                $this->replaceCompetitionShortcut($content, '/\[concours id=(\d{9}) header=\d\]/m', $competitions);
                $this->replaceCompetitionShortcut($content, '/\[concours header=\d id=(\d{9})\]/m', $competitions);
                $this->replaceCompetitionShortcut($content, '/\[concours id=(\d{9}) challenge=\d\]/m', $competitions);

                // TODO
                $pattern = '/\[challenge_calvados id=(\d{9}) classement=\d.+\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '', $content);
                }

                $pattern = '/\[video .+=\"(http.+)\"\]\[\/video]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $io->writeln('video => '.$post->getSlug().' a gérer manuellement');
                }

                $pattern = '/\[pdf src=[\'"](.+)[\'"].*\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $io->writeln('pdf => '.$post->getSlug().' a gérer manuellement');
                }

                $pattern = '/\[resequipe.*\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $io->writeln('resequipe => '.$post->getSlug().' a gérer manuellement');
                }

                $pattern = '/\[caption.+\](.+)\[\/caption\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, $matches[1][0], $content);
                }

                $pattern = '/\[adc_reservation_concours id=(\d+)\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, "L'inscription n'est plus disponible pour ce concours", $content);
                }

                $pattern = '/\[inscription_concours.+\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, "L'inscription n'est plus disponible pour ce concours", $content);
                }

                $post->setContent($content);
            }
        }

        $io->progressFinish();

        $this->em->flush();

        $posts = $this->em->getRepository(Page::class)->findAll();

        foreach ($posts as $post) {
            $content = $post->getContent();

            if ($content && preg_match_all('/\[(.+)\]/m', $content, $matches)) {
                $io->writeln($matches[1][0].' => '.$post->getId());
            }
        }

        $io->success('Finish');

        return Command::SUCCESS;
    }

    /**
     * @param array<Competition> $competitions
     *
     * @return array<int, Competition>
     */
    private function reformatCompetitionsArray(array $competitions): array
    {
        $competitionsReformatted = [];

        foreach ($competitions as $competition) {
            if ($competition->getOldId()) {
                $competitionsReformatted[$competition->getOldId()] = $competition;
            }
        }

        return $competitionsReformatted;
    }

    /**
     * @param array<int, Competition> $competitions
     */
    private function replaceCompetitionShortcut(string &$content, string $pattern, array $competitions): void
    {
        if (preg_match_all($pattern, $content, $matches)) {
            $id = $matches[1][0];
            if (isset($competitions[$id])) {
                $iframeUrl = $this->urlGenerator->generate(CompetitionController::ROUTE_LANDING_RESULTS_COMPETITION, [
                    'slug' => $competitions[$id]->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL).'?iframe=true';

                $iframe = '<iframe src="'.$iframeUrl.'" class="fit-height-content"></iframe>';

                $content = preg_replace($pattern, $iframe, $content);
            }
        }
    }
}
