<?php

declare(strict_types=1);

namespace App\Tests\Controller\Landing\Page;

use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class TeamManagementControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testPageLoadSuccessfully(): void
    {
        $client = static::createClient();

        $this->loadFixtures(['data']);

        $crawler = $client->request(Request::METHOD_GET, '/equipe-de-direction');

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1')
            ->eq(0)
            ->text();

        self::assertSame("L'équipe de direction", $title);
    }
}
