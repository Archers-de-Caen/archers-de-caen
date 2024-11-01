<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\File\Model\Photo;
use GuzzleHttp\Psr7\MimeType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class GenerateRandomPhotoService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private Filesystem $filesystem,
        private LoggerInterface $logger,
    ) {
    }

    public function generateRandomPhoto(string $environment = 'dev'): Photo
    {
        if ('test' === $environment) {
            $imageFile = $this->saveImageFromFile(__DIR__.'/../../../../database/fixtures/photo-femme-tir-a-l-arc.jpeg');
        } elseif ('dev' === $environment) {
            $imageUrl = $this->generateImageUrl();
            $imageRaw = $this->downloadImage($imageUrl);
            $imageFile = $this->saveImageFromString($imageRaw);
        } else {
            throw new \InvalidArgumentException(\sprintf('Environment "%s" is not supported.', $environment));
        }

        return $this->createPhoto($imageFile);
    }

    /**
     * Generate by https://picsum.photos/.
     */
    private function generateImageUrl(): string
    {
        $randomSize = [50, 164, 200, 236, 440, 512, 628, 851, 1024, 1050, 1080, 1280, 1440, 1920];
        $width = $randomSize[array_rand($randomSize)];
        $height = $randomSize[array_rand($randomSize)];

        return \sprintf('https://picsum.photos/seed/%s/%s', $width, $height);
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
