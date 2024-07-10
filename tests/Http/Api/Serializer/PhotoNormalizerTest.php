<?php

declare(strict_types=1);

namespace App\Tests\Http\Api\Serializer;

use App\Domain\File\Model\Photo;
use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

class PhotoNormalizerTest extends KernelTestCase
{
    use FixturesTrait;

    /**
     * @throws \JsonException
     */
    public function testPhotoSerialization(): void
    {
        /** @var SerializerInterface $serializer */
        $serializer = self::getContainer()->get(SerializerInterface::class);

        /** @var Photo $photo */
        ['photo_1' => $photo] = $this->loadFixtures(['gallery']);

        $data = $serializer->serialize($photo, 'json');

        self::assertJson($data);

        /** @var array $dataDecoded */
        $dataDecoded = json_decode($data, true, 512, \JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('url', $dataDecoded);
        self::assertArrayHasKey('urlThumbnail', $dataDecoded);
        self::assertArrayHasKey('urlThumbnailMedium', $dataDecoded);
    }
}
