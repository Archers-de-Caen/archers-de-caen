<?php

declare(strict_types=1);

namespace App\Infrastructure\LiipImagine;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CacheResolveHandler
{
    public function __construct(
        private readonly KernelInterface $kernel
    ) {
    }

    public function __invoke(CacheResolveMessage $cacheResolveMessage): void
    {
        $paths = $cacheResolveMessage->getPath();

        if (\is_string($paths)) {
            $paths = [$paths];
        }

        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'liip:imagine:cache:resolve',
            'paths' => $paths,
            '--as-script' => true,
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $output->fetch();
    }
}
