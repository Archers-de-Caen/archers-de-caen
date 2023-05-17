<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\Cms\Model\Gallery;
use App\Domain\File\Model\Photo;
use Faker;
use Fidry\AliceDataFixtures\ProcessorInterface;
use GuzzleHttp\Psr7\MimeType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GalleryProcessor implements ProcessorInterface
{
    use GenerateRandomPhotoTrait;

    private Faker\Generator $faker;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
    ) {
        $this->faker = Faker\Factory::create('fr_FR');
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess(string $id, object $object): void
    {
        if (!$object instanceof Gallery) {
            return;
        }

        $object->setMainPhoto($this->generateRandomPhoto());

        if ('gallery_100_photos' === $id) {
            $photoNumber = 100;
        } else {
            $photoNumber = $this->faker->numberBetween(3, 25);
        }

        for ($i = 0; $i < $photoNumber; ++$i) {
            $object->addPhoto($this->generateRandomPhoto());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess(string $id, object $object): void
    {
        // do nothing
    }
}
