<?php

declare(strict_types=1);

namespace App\Tests\Controller\Landing;

use App\Http\Landing\Controller\ContactController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * @internal
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrlsSuccessfulWithoutAuthentication
     */
    public function testPageIsSuccessfulWithoutAuthentication(string $route, string $method): void
    {
        $client = self::createClient();

        /** @var Router $router */
        $router = $client->getContainer()->get('router');

        $client->request($method, $router->generate($route));

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return array<array{string, string}>
     */
    public function provideUrlsSuccessfulWithoutAuthentication(): array
    {
        return [
            ['landing_index', Request::METHOD_GET],
            [ContactController::ROUTE_LANDING_CONTACT, Request::METHOD_GET],
            ['landing_style_guide', Request::METHOD_GET],
        ];
    }
}
