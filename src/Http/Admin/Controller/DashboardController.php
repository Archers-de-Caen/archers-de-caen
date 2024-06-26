<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\License;
use App\Domain\Badge\Model\Badge;
use App\Domain\Cms\Model\Data;
use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Model\Tag;
use App\Domain\Competition\Model\Competition;
use App\Domain\File\Model\Document;
use App\Domain\File\Model\Photo;
use App\Domain\Result\Model\ResultBadge;
use App\Http\Admin\Controller\Badge\ResultBadgeFederalHonorCrudController;
use App\Http\Admin\Controller\Badge\ResultBadgeProgressArrowCrudController;
use App\Http\Admin\Controller\Cms\ActualityCrudControllerAbstract;
use App\Http\Admin\Controller\Cms\PageCrudController;
use App\Http\Admin\Controller\Developer\Liip\ListPathsLiipAdminController;
use App\Http\Admin\Controller\File\DocumentCrudController;
use App\Http\Admin\Controller\File\NewspaperCrudControllerAbstract;
use App\Http\Landing\Controller\IndexController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

final class DashboardController extends AbstractDashboardController
{
    public const string ROUTE = 'admin_index';

    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    #[Route('/', name: self::ROUTE)]
    #[\Override]
    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/index.html.twig', [
            'dashboard_controller_filepath' => (new \ReflectionClass(static::class))->getFileName(),
        ]);
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Archers De Caen V3')
        ;
    }

    #[\Override]
    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->addFormTheme('form/ckeditor.html.twig')
        ;
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard("Page d'accueil", 'fa fa-home');

        yield MenuItem::section();
        yield MenuItem::linkToCrud('Actualité', 'fas fa-newspaper', Page::class)
            ->setController(ActualityCrudControllerAbstract::class);
        yield MenuItem::linkToCrud('Page', 'fas fa-pager', Page::class)
            ->setController(PageCrudController::class);
        yield MenuItem::linkToCrud('Element de page', 'fas fa-database', Data::class);
        yield MenuItem::linkToCrud('Tags', 'fas fa-tags', Tag::class)
            ->setPermission(Archer::ROLE_DEVELOPER);

        yield MenuItem::section();
        yield MenuItem::linkToCrud('Archer', 'fas fa-running', Archer::class);
        yield MenuItem::linkToCrud('Licence', 'fas fa-id-badge', License::class);

        yield MenuItem::section();
        yield MenuItem::linkToCrud('Competition', 'fas fa-star', Competition::class);
        yield MenuItem::linkToCrud('Flèche de progression', 'fas fa-bullseye', ResultBadge::class)
            ->setController(ResultBadgeProgressArrowCrudController::class);

        yield MenuItem::section();
        yield MenuItem::linkToCrud('Badge', 'fas fa-bullseye', Badge::class)
            ->setPermission(Archer::ROLE_DEVELOPER);
        yield MenuItem::linkToCrud('Distinction fédéral', 'fas fa-bullseye', ResultBadge::class)
            ->setController(ResultBadgeFederalHonorCrudController::class)
            ->setPermission(Archer::ROLE_DEVELOPER);

        yield MenuItem::section();
        yield MenuItem::linkToCrud('Galerie', 'fas fa-images', Gallery::class);
        yield MenuItem::linkToCrud('Photo', 'fas fa-image', Photo::class);
        yield MenuItem::linkToCrud('Document', 'fas fa-file', Document::class)
            ->setController(DocumentCrudController::class);
        yield MenuItem::linkToCrud('Gazette', 'fas fa-newspaper', Document::class)
            ->setController(NewspaperCrudControllerAbstract::class);

        yield MenuItem::section();
        yield MenuItem::linktoRoute('Liip image cache', 'fa fa-clock-rotate-left', ListPathsLiipAdminController::ROUTE)
            ->setPermission(Archer::ROLE_DEVELOPER)
        ;

        yield MenuItem::section();
        yield MenuItem::linkToRoute('Revenir au site', 'fas fa-left-long', IndexController::ROUTE);

        if ($this->isGranted(AuthenticatedVoter::IS_IMPERSONATOR, $this->getUser())) {
            yield MenuItem::linkToExitImpersonation('Revenir sur son compte', 'fas fa-portal-exit')
                ->setPermission(Archer::ROLE_DEVELOPER);
        }

        yield MenuItem::linkToLogout('Déconnexion', 'fas fa-arrow-right-from-bracket');

        /** @var string $version */
        $version = $this->parameterBag->get('app.version');
        yield MenuItem::section($version);
    }

    #[\Override]
    public function configureAssets(): Assets
    {
        return Assets::new()
            /* TODO: maybe un jour ->addJsFile('https://ckeditor.com/apps/ckfinder/3.5.0/ckfinder.js') */
            ->addWebpackEncoreEntry('admin')
        ;
    }
}
