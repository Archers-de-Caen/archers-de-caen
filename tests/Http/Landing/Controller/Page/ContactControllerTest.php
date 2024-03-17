<?php

declare(strict_types=1);

namespace App\Tests\Http\Landing\Controller\Page;

use App\Http\Landing\Controller\Page\ContactController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * @internal
 */
class ContactControllerTest extends WebTestCase
{
    public function testPageIsSuccessfulWithoutAuthentication(): void
    {
        $client = self::createClient();

        /** @var Router $router */
        $router = $client->getContainer()->get('router');

        $client->request(Request::METHOD_GET, $router->generate(ContactController::ROUTE));

        self::assertResponseIsSuccessful();
    }
}
