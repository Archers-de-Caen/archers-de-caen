<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Model\Photo;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use DOMElement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[AsCommand(
    name: 'app:v2-to-v3:wp-posts',
    description: 'Migration des actualités de la version 2 du site vers la 3',
)]
class V2ToV3ActualityCommand extends Command
{
    use ArcherTrait;
    use DownloadTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly UploaderHelper $uploaderHelper,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $archer = $this->em->getRepository(Archer::class)->findOneBy(['licenseNumber' => '210212D']); // TODO: A changer

        if (!$archer) {
            $io->error('Archer introuvable');

            return self::FAILURE;
        }

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery("SELECT * FROM wp_posts WHERE post_type in ('page', 'post') AND post_content != ''", $rsm);

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('post_date', 'createdAt', Types::DATETIME_IMMUTABLE);
        $rsm->addScalarResult('post_title', 'title');
        $rsm->addScalarResult('post_content', 'content');
        $rsm->addScalarResult('post_status', 'status');
        $rsm->addScalarResult('post_type', 'type');

        /** @var array<array> $posts */
        $posts = $nativeQuery->getArrayResult();

        $io->progressStart(count($posts));

        foreach ($posts as $post) {
            $io->progressAdvance();

            $status = match ($post['status']) {
                'publish' => Status::PUBLISH,
                'trash' => Status::DELETE,
                default => Status::DRAFT,
            };

            $mainImage = null;

            $crawler = new Crawler($post['content']);

            /*
             * Permet de récupérer les images du poste est de les enregistrer
             */
            $crawler->filter('img')->each(function (Crawler $crawler, $i) use (&$mainImage) {
                /** @var DOMElement $node */
                foreach ($crawler as $node) {
                    $src = $crawler->attr('src') ?? '';

                    if (!$src) {
                        return $crawler;
                    }

                    if (str_starts_with($src, '[')) {
                        return $crawler;
                    }

                    if (str_starts_with($src, '/')) {
                        $src = 'https://www.archers-caen.fr'.$src;
                    }

                    /** @var Photo $image */
                    $image = $this->downloadFile($src);

                    $this->em->persist($image);

                    if ($newSrc = $this->uploaderHelper->asset($image)) {
                        $node->setAttribute('src', $newSrc);
                    }

                    $crawler->html($node->nodeValue);

                    $crawler->getNode(0)?->parentNode?->insertBefore(new DOMElement('br'), $node->nextSibling);

                    if (0 === $i) {
                        $mainImage = $image;
                    }
                }

                return $crawler;
            });

            $content = $crawler->html();

            $content = str_replace('center', 'p', $content);

            $newPage = (new Page())
                ->setTitle($post['title'])
                ->setCreatedAt($post['createdAt'])
                ->setCreatedBy($archer)
                ->setStatus($status)
                ->setContent($content)
                ->setImage($mainImage);

            if ('page' === $post['type']) {
                $newPage->setCategory(Category::PAGE);
            } elseif ('post' === $post['type']) {
                $newPage->setCategory(Category::ACTUALITY);
            } else {
                $io->error('WTF !');

                continue;
            }

            if ($this->validator->validate($newPage)->count()) {
                $io->error('La validation de la page "'.$post['title'].'" a échoué');

                continue;
            }

            $this->em->persist($newPage);
        }

        $io->progressFinish();

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }
}
