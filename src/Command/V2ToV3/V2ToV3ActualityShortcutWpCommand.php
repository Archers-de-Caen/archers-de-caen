<?php

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Form\Photo\PhotoFormType;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Model\Photo;
use App\Domain\Competition\Model\Competition;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use DOMElement;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[AsCommand(
    name: 'app:v2-to-v3:wp-posts-shortcut',
    description: 'Migration des actualités de la version 2 du site vers la 3',
)]
class V2ToV3ActualityShortcutWpCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
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

                $pattern = '/\[challenge_calvados id=(\d{9}) classement=\d.+\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '', $content);
                }

                $pattern = '/\[adc_reservation_concours id=(\d+)\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '$id', $content);

                }

                $pattern = '/\[video .+=\"(http.+)\][\\video]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '$id', $content);

                }

                $pattern = '/\[pdf .+=(.+)\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '$id', $content);

                }

                $pattern = '/\[resequipe.*\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '$id', $content);

                }

                $pattern = '/\[caption.+\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '$id', $content);

                }

                $pattern = '/\[inscription_concours.+\]/m';
                if (preg_match_all($pattern, $content, $matches)) {
                    $content = preg_replace($pattern, '$id', $content);
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
                $io->writeln($matches[1][0] . ' => ' . $post->getId());
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

    private function replaceCompetitionShortcut(string &$content, string $pattern, array $competitions): void
    {
        if (preg_match_all($pattern, $content, $matches)) {
            $id = $matches[1][0];
            $content = preg_replace($pattern, '$id', $content);

            if (isset($competitions[$id])) { // todo: marche pas
                $content = preg_replace($pattern, '$id', $content);
            }
        }
    }
}
