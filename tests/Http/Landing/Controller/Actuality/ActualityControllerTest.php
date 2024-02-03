<?php

declare(strict_types=1);

namespace App\Tests\Http\Landing\Controller\Actuality;

use App\Domain\Cms\Model\Page;
use App\Tests\Ressources\Services\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class ActualityControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testPageLoadSuccessfully(): void
    {
        $client = static::createClient();

        /** @var Page $page */
        ['actuality_with_real_content' => $page] = $this->loadFixtures(['page']);

        $crawler = $client->request(Request::METHOD_GET, '/actualite/'.$page->getSlug());

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1')
            ->eq(0)
            ->text();

        self::assertSame($page->getTitle(), $title);
    }
}
