<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\File\Model\Photo;
use GuzzleHttp\Psr7\MimeType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

trait GenerateRandomPhotoTrait
{
    private function generateRandomPhoto(): Photo
    {
        $imageUrl = $this->generateImageUrl();
        $imageRaw = $this->downloadImage($imageUrl);
        $imageFile = $this->saveImage($imageRaw);

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
        } catch (ExceptionInterface $e) {
            $this->logger->error($e->getMessage());

            return '';
        }
    }

    private function saveImage(string $imageContent): UploadedFile
    {
        $tempFile = $this->filesystem->tempnam('/fixtures', 'random-photo');

        $this->filesystem->dumpFile($tempFile, $imageContent);

        return new UploadedFile($tempFile, basename($tempFile), MimeType::fromFilename($tempFile), test: true);
    }

    private function createPhoto(UploadedFile $uploadedFile): Photo
    {
        return (new Photo())
            ->setImageFile($uploadedFile)
        ;
    }
}
