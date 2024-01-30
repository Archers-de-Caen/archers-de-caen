<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\Cms\Model\Page;
use Faker;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PageProcessor implements ProcessorInterface
{
    use GenerateRandomPhotoTrait;

    private Faker\Generator $faker;

    public function __construct(
        HttpClientInterface $httpClient,
        Filesystem $filesystem,
        LoggerInterface $logger,
        private readonly string $env,
    ) {
        $this->faker = Faker\Factory::create('fr_FR');

        $this->setFilesystem($filesystem);
        $this->setHttpClient($httpClient);
        $this->setLogger($logger);
        $this->setFaker($this->faker);
    }

    public function preProcess(string $id, object $object): void
    {
        if (!$object instanceof Page) {
            return;
        }

        $object->setImage($this->generateRandomPhoto($this->env));
    }

    public function postProcess(string $id, object $object): void
    {
        // do nothing
    }
}
