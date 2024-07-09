<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\Cms\Model\Gallery;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Random\RandomException;

final readonly class GalleryProcessor implements ProcessorInterface
{
    public function __construct(
        private GenerateRandomPhotoService $generateRandomPhotoService,
        private string $env,
    ) {
    }

    /**
     * @throws RandomException
     */
    #[\Override]
    public function preProcess(string $id, object $object): void
    {
        if (!$object instanceof Gallery) {
            return;
        }

        $object->setMainPhoto($this->generateRandomPhotoService->generateRandomPhoto($this->env));

        $photoNumber = 'gallery_100_photos' === $id ? 100 : random_int(3, 25);

        for ($i = 0; $i < $photoNumber; ++$i) {
            $object->addPhoto($this->generateRandomPhotoService->generateRandomPhoto($this->env));
        }
    }

    #[\Override]
    public function postProcess(string $id, object $object): void
    {
        // do nothing
    }
}
