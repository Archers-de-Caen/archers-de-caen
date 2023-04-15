<?php

declare(strict_types=1);

namespace App\Tests\Controller\Landing;

use App\Http\Landing\Controller\IndexController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * @internal
 */
class IndexControllerTest extends WebTestCase
{
    public function testPageIsSuccessfulWithoutAuthentication(): void
    {
        $client = self::createClient();

        /** @var Router $router */
        $router = $client->getContainer()->get('router');

        $client->request(Request::METHOD_GET, $router->generate(IndexController::ROUTE));

        self::assertResponseIsSuccessful();
    }
}
