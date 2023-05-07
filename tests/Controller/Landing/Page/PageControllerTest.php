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
class PageControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testPageLoadSuccessfully(): void
    {
        $client = static::createClient();

        /** @var Page $page */
        ['page_with_real_content' => $page] = $this->loadFixtures(['page']);

        $crawler = $client->request(Request::METHOD_GET, '/p/'.$page->getSlug());

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1')
            ->eq(0)
            ->text();

        self::assertSame($page->getTitle(), $title);
    }
}
