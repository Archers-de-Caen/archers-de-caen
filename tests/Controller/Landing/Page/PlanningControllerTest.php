<?php

declare(strict_types=1);

namespace App\Tests\Controller\Landing\Page;

use App\Domain\Cms\Model\Page;
use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class PlanningControllerTest extends WebTestCase
{
    public function testPageLoadSuccessfully(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, '/planning');

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1')
            ->eq(0)
            ->text();

        self::assertSame('Créneaux d’entraînement', $title);
    }
}