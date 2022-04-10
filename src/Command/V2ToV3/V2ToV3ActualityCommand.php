<?php

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Model\Photo;
use Doctrine\DBAL\Types\DateTimeType;
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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

#[AsCommand(
    name: 'app:v2-to-v3:wp-posts',
    description: 'Migration des actualités de la version 2 du site vers la 3',
)]
class V2ToV3ActualityCommand extends Command
{
    use ArcherTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private UploaderHelper $uploaderHelper,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $archer = $this->em->getRepository(Archer::class)->findOneBy(['licenseNumber' => '123459A']); // TODO: A changer

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
        $rsm->addScalarResult('post_name', 'slug');

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

            /**
             * Permet de récupérer les images du poste est de les enregistrer
             */
            $crawler->filter('img')->each(function (Crawler $crawler, $i) use (&$mainImage, $io) {
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
                        $src = 'https://www.archers-caen.fr' . $src;
                    }

                    $name = explode('/', $src)[count(explode('/', $src)) - 1];
                    $extension = explode('.', $name)[count(explode('.', $name)) - 1];

                    if (!$filePath = tempnam(sys_get_temp_dir(), 'adc_image')) {
                        $io->error('tempnam bug');

                        return self::FAILURE;
                    }

                    if (!$file = fopen($filePath, 'w')) {
                        $io->error('fopen bug');

                        return self::FAILURE;
                    }

                    fwrite($file, @file_get_contents($src) ?: ''); /** @ for ignore warning like http 404 error */
                    fclose($file);

                    $uploadedImage = new UploadedFile($filePath, $name);

                    $image = (new Photo())->setImageFile($uploadedImage);

                    $this->em->persist($image);

                    if ($newSrc = $this->uploaderHelper->asset($image)) {
                        $node->setAttribute('src', $newSrc);
                    }

                    $crawler->html($node->nodeValue);

                    if ($i === 0) {
                        $mainImage = $image;
                    }
                }

                return $crawler;
            });

            $newPage = (new Page())
                ->setTitle($post['title'])
                ->setCreatedAt($post['createdAt'])
                ->setCreatedBy($archer)
                ->setSlug($post['slug'])
                ->setStatus($status)
                ->setContent($crawler->html())
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
                $io->error('La validation de la page "' . $post['title'] . '" a échoué');

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
