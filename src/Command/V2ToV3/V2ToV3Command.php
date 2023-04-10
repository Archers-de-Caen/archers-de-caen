<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:v2-to-v3',
    description: 'Migration des records de la version 2 du site vers la 3',
)]
class V2ToV3Command extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$application = $this->getApplication()) {
            return self::FAILURE;
        }

        try {
            $commands = [
                'app:v2-to-v3:wp-posts',
                'app:v2-to-v3:badge',
                'app:v2-to-v3:progress-arrow',
                'app:v2-to-v3:competition',
                'app:v2-to-v3:wp-posts-shortcut',
            ];

            foreach ($commands as $defaultName) {
                $io->title($defaultName);

                $command = $application->find($defaultName);

                $command->run($input, $output);
            }
        } catch (\Exception|ExceptionInterface $e) {
            $io->error($e->getMessage());

            return self::FAILURE;
        }

        $io->success('Finish');

        return Command::SUCCESS;
    }
}
