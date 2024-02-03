<?php

declare(strict_types=1);

namespace App\Tests\Http\Landing\Controller\Page;

use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class ClubControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testPageIsSuccessfulWithoutAuthentication(): void
    {
        $client = self::createClient();

        $this->loadFixtures(['data']);

        $crawler = $client->request(Request::METHOD_GET, '/club');

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1.title')
            ->eq(0)
            ->text()
        ;

        self::assertSame('Le club', $title);
    }
}
