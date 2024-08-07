<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Archer\Model\Archer;
use App\Helper\PaginatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/style-guide',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class StyleGuideController extends AbstractController
{
    public const string ROUTE = 'landing_style_guide';

    public function __invoke(
        Request $request,
        #[Autowire(env: 'APP_PASSWORD_STYLE_GUIDE')]
        string $password,
        #[MapQueryParameter('password')]
        ?string $userPassword = null,
    ): Response {
        if (
            !$this->isGranted(Archer::ROLE_DEVELOPER)
            && 'dev' !== $request->server->get('APP_ENV')
            && $password !== $userPassword
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('/landing/style-guide/style-guide.html.twig', [
            'paginator' => PaginatorHelper::pagination((int) ($request->query->get('page') ?: 1), 100),
        ]);
    }
}
