<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Archer\Model\Archer;
use App\Helper\PaginatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/style-guide',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class StyleGuideController extends AbstractController
{
    public const ROUTE = 'landing_style_guide';

    public function __invoke(Request $request): Response
    {
        if (!$this->isGranted(Archer::ROLE_DEVELOPER) && 'dev' !== $request->server->get('APP_ENV')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('/landing/style-guide/style-guide.html.twig', [
            'paginator' => PaginatorHelper::pagination((int) ($request->query->get('page') ?: 1), 100),
        ]);
    }
}
