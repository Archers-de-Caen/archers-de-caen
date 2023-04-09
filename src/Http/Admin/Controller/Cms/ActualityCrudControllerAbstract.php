<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Cms;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Domain\Newsletter\NewsletterType;
use App\Http\Landing\Controller\ActualitiesController;
use App\Infrastructure\Mailing\GalleryNewsletterMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActualityCrudControllerAbstract extends AbstractPageCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        parent::configureCrud($crud);

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des actualités du site')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une actualité au site')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Page $page) => sprintf('Edition de l\'actualité <b>%s</b>', $page))
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->where(sprintf("entity.category = '%s'", Category::ACTUALITY->value))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        parent::configureActions($actions);

        $publicLink = Action::new('public-link')
            ->setLabel('Lien public')
            ->linkToUrl(function (Page $page) {
                return $this->urlGenerator->generate(ActualitiesController::ROUTE_LANDING_ACTUALITY, [
                    'slug' => $page->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $publicLink);
    }

    /**
     * @param Page $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setCategory(Category::ACTUALITY);

        parent::persistEntity($entityManager, $entityInstance);
    }
}
