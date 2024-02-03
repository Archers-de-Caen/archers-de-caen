<?php

declare(strict_types=1);

namespace App\Tests\Http\Landing\Controller\Actuality;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class ActualitiesControllerTest extends WebTestCase
{
    public function testPageLoadSuccessfully(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, '/actualites');

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1')
            ->eq(0)
            ->text();

        self::assertSame('L’actualité du Club', $title);
    }
}
