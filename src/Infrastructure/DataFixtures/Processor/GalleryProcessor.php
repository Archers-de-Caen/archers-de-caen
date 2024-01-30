<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\Cms\Model\Gallery;
use Faker;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GalleryProcessor implements ProcessorInterface
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
        if (!$object instanceof Gallery) {
            return;
        }

        $object->setMainPhoto($this->generateRandomPhoto($this->env));

        if ('gallery_100_photos' === $id) {
            $photoNumber = 100;
        } else {
            $photoNumber = $this->faker->numberBetween(3, 25);
        }

        for ($i = 0; $i < $photoNumber; ++$i) {
            $object->addPhoto($this->generateRandomPhoto($this->env));
        }
    }

    public function postProcess(string $id, object $object): void
    {
        // do nothing
    }
}
