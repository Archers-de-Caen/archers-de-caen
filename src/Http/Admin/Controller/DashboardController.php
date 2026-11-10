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
use App\Http\Admin\Controller\Badge\BadgeCrudController;
use App\Http\Admin\Controller\Badge\ResultBadgeFederalHonorCrudController;
use App\Http\Admin\Controller\Badge\ResultBadgeProgressArrowCrudController;
use App\Http\Admin\Controller\Cms\ActualityCrudControllerAbstract;
use App\Http\Admin\Controller\Cms\DataCrudController;
use App\Http\Admin\Controller\Cms\PageCrudController;
use App\Http\Admin\Controller\Cms\TagCrudController;
use App\Http\Admin\Controller\File\DocumentCrudController;
use App\Http\Admin\Controller\File\NewspaperCrudControllerAbstract;
use App\Http\Admin\Controller\File\PhotoCrudController;
use App\Http\Landing\Controller\IndexController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
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
            'dashboard_controller_filepath' => (new \ReflectionClass(self::class))->getFileName(),
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
        yield MenuItem::linkTo(ActualityCrudControllerAbstract::class, 'Actualité', 'fas fa-newspaper');
        yield MenuItem::linkTo(PageCrudController::class, 'Page', 'fas fa-pager');
        yield MenuItem::linkTo(DataCrudController::class, 'Element de page', 'fas fa-database');
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fas fa-tags')
            ->setPermission(Archer::ROLE_DEVELOPER);

        yield MenuItem::section();
        yield MenuItem::linkTo(ArcherCrudController::class, 'Archer', 'fas fa-running');
        yield MenuItem::linkTo(LicenseCrudController::class, 'Licence', 'fas fa-id-badge');

        yield MenuItem::section();
        yield MenuItem::linkTo(CompetitionCrudController::class, 'Competition', 'fas fa-star');
        yield MenuItem::linkTo(ResultBadgeProgressArrowCrudController::class, 'Flèche de progression', 'fas fa-bullseye');

        yield MenuItem::section();
        yield MenuItem::linkTo(BadgeCrudController::class, 'Badge', 'fas fa-bullseye')
            ->setPermission(Archer::ROLE_DEVELOPER);
        yield MenuItem::linkTo(ResultBadgeFederalHonorCrudController::class, 'Distinction fédéral', 'fas fa-bullseye')
            ->setPermission(Archer::ROLE_DEVELOPER);

        yield MenuItem::section();
        yield MenuItem::linkTo(GalleryCrudController::class, 'Galerie', 'fas fa-images');
        yield MenuItem::linkTo(PhotoCrudController::class, 'Photo', 'fas fa-image');
        yield MenuItem::linkTo(DocumentCrudController::class, 'Document', 'fas fa-file');
        yield MenuItem::linkTo(NewspaperCrudControllerAbstract::class, 'Gazette', 'fas fa-newspaper');

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
