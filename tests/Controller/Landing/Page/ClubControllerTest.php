<?php

declare(strict_types=1);

namespace App\Tests\Controller\Landing\Page;

use App\Http\Landing\Controller\Page\ClubController;
use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

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
