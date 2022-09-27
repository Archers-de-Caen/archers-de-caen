<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Cms;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Http\Landing\Controller\ActualitiesController;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageCrudControllerAbstract extends AbstractPageCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        parent::configureCrud($crud);

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des pages du site')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une page au site')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Page $page) => sprintf('Edition de la page <b>%s</b>', $page))
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->where(sprintf("entity.category = '%s'", Category::PAGE->value))
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

    public function configureFields(string $pageName): iterable
    {
        $tags = AssociationField::new('tags')
            ->setLabel('Tags')
            ->formatValue(static fn ($value, Page $page) => implode(',', $page->getTags()->toArray()))
        ;

        $fields = (array) parent::configureFields($pageName);

        return array_merge($fields, [$tags]);
    }

    /**
     * @param Page $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setCategory(Category::PAGE);

        parent::persistEntity($entityManager, $entityInstance);
    }
}