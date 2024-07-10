<?php

declare(strict_types=1);

namespace App\Infrastructure\DataFixtures\Processor;

use App\Domain\Cms\Model\Page;
use Fidry\AliceDataFixtures\ProcessorInterface;

final readonly class PageProcessor implements ProcessorInterface
{
    public function __construct(
        private GenerateRandomPhotoService $generateRandomPhotoService,
        private string $env,
    ) {
    }

    #[\Override]
    public function preProcess(string $id, object $object): void
    {
        if (!$object instanceof Page) {
            return;
        }

        $object->setImage($this->generateRandomPhotoService->generateRandomPhoto($this->env));
    }

    #[\Override]
    public function postProcess(string $id, object $object): void
    {
        // do nothing
    }
}
