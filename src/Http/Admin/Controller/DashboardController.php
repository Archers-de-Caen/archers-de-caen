<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\License;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Model\Photo;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Model\CompetitionRegister;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/', name: 'admin_index')]
    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/index.html.twig', [
            'dashboard_controller_filepath' => (new \ReflectionClass(static::class))->getFileName(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Archers De Caen V3')
        ;
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Page d\'accueil', 'fa fa-home'),

            MenuItem::section(),
            MenuItem::linkToCrud('Archer', 'fas fa-running', Archer::class),
            MenuItem::linkToCrud('Licence', 'fas fa-id-badge', License::class),

            MenuItem::section(),
            MenuItem::linkToCrud('Competition', 'fas fa-star', Competition::class),
            MenuItem::linkToCrud('Inscription Caen', 'fas fa-star', CompetitionRegister::class),

            MenuItem::section(),
            MenuItem::linkToCrud('Galerie', 'fas fa-images', Gallery::class),
            MenuItem::linkToCrud('Photo', 'fas fa-image', Photo::class),

            MenuItem::section(),
            MenuItem::linkToCrud('Page', 'fas fa-pager', Page::class)
                ->setQueryParameter('filters', ['category' => ['value' => Category::PAGE->value, 'comparison' => '=']]),
            MenuItem::linkToCrud('Actualité', 'fas fa-newspaper', Page::class)
                ->setQueryParameter('filters', ['category' => ['value' => Category::ACTUALITY->value, 'comparison' => '=']]),
        ];
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            /* TODO: maybe un jour ->addJsFile('https://ckeditor.com/apps/ckfinder/3.5.0/ckfinder.js') */
            ->addWebpackEncoreEntry('admin')
        ;
    }
}
