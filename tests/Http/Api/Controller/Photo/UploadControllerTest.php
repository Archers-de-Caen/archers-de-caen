<?php

declare(strict_types=1);

namespace App\Tests\Http\Api\Controller\Photo;

use App\Domain\Archer\Model\Archer;
use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UploadControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testUploadPhoto(): void
    {
        $client = self::createClient();

        /** @var Archer $user */
        ['archer_admin' => $user] = $this->loadFixtures(['archer']);

        $client->loginUser($user);

        $fixturesPath = __DIR__.'/../../../../../fixtures';

        $copyResult = copy(
            from: $fixturesPath.'/photo-femme-tir-a-l-arc.jpeg',
            to: $fixturesPath.'/photo-copy.jpeg',
        );

        if (!$copyResult) {
            self::fail('Unable to copy photo.jpeg');
        }

        $image = new UploadedFile(
            path: $fixturesPath.'/photo-copy.jpeg',
            originalName: 'photo.jpeg',
        );

        $client->request(Request::METHOD_POST, '/api/photos', [], [
            'imageFile' => $image,
        ]);

        $this->assertResponseIsSuccessful();
    }
}
