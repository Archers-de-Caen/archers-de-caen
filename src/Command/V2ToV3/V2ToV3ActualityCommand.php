<?php

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:v2-to-v3:wp-posts',
    description: 'Migration des actualités de la version 2 du site vers la 3',
)]
class V2ToV3ActualityCommand extends Command
{
    use ArcherTrait;

    public function __construct(private EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery("SELECT * FROM wp_posts WHERE post_type in ('page', 'post')", $rsm);

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('post_date', 'createdDate');
        $rsm->addScalarResult('post_title', 'title');
        $rsm->addScalarResult('post_content', 'content');
        $rsm->addScalarResult('post_status', 'status');
        $rsm->addScalarResult('post_type', 'type');

        /** @var array<array> $posts */
        $posts = $nativeQuery->getArrayResult();

        foreach (array_filter($posts, fn (array $post) => $post['type'] === 'page') as $page) {
            $io->writeln($page);

            break;
        }

        foreach (array_filter($posts, fn (array $post) => $post['type'] === 'post') as $post) {
            $io->writeln($post);

            break;
        }

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }
}
