<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\File\Model\Photo;
use Faker\Generator;
use GuzzleHttp\Psr7\MimeType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait GenerateRandomPhotoTrait
{
    private HttpClientInterface $httpClient;

    private Filesystem $filesystem;

    private LoggerInterface $logger;

    private Generator $faker;

    private function setFilesystem(Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    private function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    private function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function setFaker(Generator $faker): void
    {
        $this->faker = $faker;
    }

    private function generateRandomPhoto(string $environment = 'dev'): Photo
    {
        if ('test' === $environment) {
            $imageFile = $this->saveImageFromFile(__DIR__.'/../../../../fixtures/photo-femme-tir-a-l-arc.jpeg');
        } elseif ('dev' === $environment) {
            $imageUrl = $this->generateImageUrl();
            $imageRaw = $this->downloadImage($imageUrl);
            $imageFile = $this->saveImageFromString($imageRaw);
        } else {
            throw new \InvalidArgumentException(sprintf('Environment "%s" is not supported.', $environment));
        }

        return $this->createPhoto($imageFile);
    }

    private function generateImageUrl(): string
    {
        $randomSize = [50, 164, 200, 236, 440, 512, 628, 851, 1024, 1050, 1080, 1280, 1440, 1920];
        $words = [
            'cat', 'dog', 'bird',
            'man', 'woman', 'archer',
            'target', 'bow', 'longbow', 'compound', 'arrow',
        ];

        /** @var string $keyword */
        $keyword = $this->faker->randomElement($words);

        /** @var int $width */
        $width = $this->faker->randomElement($randomSize);

        /** @var int $height */
        $height = $this->faker->randomElement($randomSize);

        return sprintf('https://source.unsplash.com/random/%s×%s/?%s', $width, $height, $keyword);
    }

    private function downloadImage(string $imageUrl): string
    {
        try {
            return $this->httpClient->request(Request::METHOD_GET, $imageUrl)->getContent();
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());

            return '';
        }
    }

    private function saveImageFromString(string $imageContent): UploadedFile
    {
        $tempFile = $this->filesystem->tempnam('/fixtures', 'random-photo');

        $this->filesystem->dumpFile($tempFile, $imageContent);

        return new UploadedFile(
            path: $tempFile,
            originalName: basename($tempFile),
            mimeType: MimeType::fromFilename($tempFile),
            test: true
        );
    }

    private function saveImageFromFile(string $filePath): UploadedFile
    {
        $tempFile = $this->filesystem->tempnam('/fixtures', 'random-photo');

        $this->filesystem->copy($filePath, $tempFile);

        return new UploadedFile(
            path: $tempFile,
            originalName: basename($tempFile),
            mimeType: MimeType::fromFilename($tempFile),
            test: true
        );
    }

    private function createPhoto(UploadedFile $uploadedFile): Photo
    {
        return (new Photo())
            ->setImageFile($uploadedFile)
        ;
    }
}
