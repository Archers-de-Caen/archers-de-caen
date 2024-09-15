<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Cms\Model\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/p/nos-outils-de-communication',
    name: self::ROUTE,
    methods: Request::METHOD_GET,
    priority: 10
)]
final class CommunicationController extends AbstractController
{
    public const string ROUTE = 'landing_page_communication';

    public function __invoke(Page $page): Response
    {
        return $this->render('/landing/pages/communication.html.twig', [
            'page' => $page,
        ]);
    }
}
