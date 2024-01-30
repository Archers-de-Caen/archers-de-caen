<?php

declare(strict_types=1);

namespace App\Tests\Http\Landing\Controller\Page;

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
