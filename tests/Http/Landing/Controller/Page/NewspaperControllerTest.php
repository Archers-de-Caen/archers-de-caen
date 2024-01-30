<?php

declare(strict_types=1);

namespace App\Tests\Http\Landing\Controller\Page;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class NewspaperControllerTest extends WebTestCase
{
    public function testNewspaperPageLoadSuccessfully(): void
    {
        $client = static::createClient();

        $crawler = $client->request(Request::METHOD_GET, '/gazettes');

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1.title')
            ->eq(0)
            ->text();

        self::assertSame('La gazette', $title);
    }
}
