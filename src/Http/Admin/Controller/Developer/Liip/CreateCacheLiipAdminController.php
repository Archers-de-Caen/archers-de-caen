<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Developer\Liip;

use App\Http\Admin\Controller\DashboardController;
use App\Infrastructure\LiipImagine\CacheResolveMessage;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/liip/create-image-cache',
    name: self::ROUTE,
    methods: Request::METHOD_GET,
)]
class CreateCacheLiipAdminController extends AbstractController
{
    public const ROUTE = 'admin_developer_admin_liip_image_cache_create';

    /**
     * @throws \Exception
     */
    public function __invoke(
        Request $request,
        MessageBusInterface $bus,
        AdminUrlGenerator $adminUrlGenerator
    ): Response {
        $redirect = $this->redirect(
            $adminUrlGenerator
                ->setDashboard(DashboardController::class)
                ->setRoute(ListPathsLiipAdminController::ROUTE)
                ->generateUrl()
        );

        /** @var ?string $path */
        $path = $request->query->get('path');

        if (!$path) {
            return $redirect;
        }

        if (str_contains($path, '/')) {
            $pathExplode = explode('/', $path);
        } else {
            $pathExplode = explode('\\', $path);
        }

        $path = $pathExplode[\count($pathExplode) - 1];

        $bus->dispatch(new CacheResolveMessage($path));

        $this->addFlash('info', 'Cache creation in progress');

        return $redirect;
    }
}
