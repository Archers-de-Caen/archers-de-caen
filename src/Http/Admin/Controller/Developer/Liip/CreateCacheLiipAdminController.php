<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Developer\Liip;

use App\Http\Admin\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
        KernelInterface $kernel,
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

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'liip:imagine:cache:resolve',
            'paths' => [$path],
            '--as-script' => true,
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();
        $application->run($input, $output);

        // return the output, don't use if you used NullOutput()
        $content = $output->fetch();

        $this->addFlash('info', $content);

        return $redirect;
    }
}
