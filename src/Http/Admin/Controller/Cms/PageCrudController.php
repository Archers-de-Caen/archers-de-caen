<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Cms;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Http\Landing\Controller\Actuality\ActualityController;
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

final class PageCrudController extends AbstractPageCrudController
{
    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        parent::configureCrud($crud);

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des pages du site')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une page au site')
            ->setPageTitle(Crud::PAGE_EDIT, static fn(Page $page): string => sprintf('Edition de la page <b>%s</b>', $page))
        ;
    }

    #[\Override]
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->where(sprintf("entity.category = '%s'", Category::PAGE->value))
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        parent::configureActions($actions);

        $publicLink = Action::new('public-link')
            ->setLabel('Lien public')
            ->linkToUrl(function (Page $page): string {
                return $this->urlGenerator->generate(ActualityController::ROUTE, [
                    'slug' => $page->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $publicLink);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $tags = AssociationField::new('tags')
            ->setLabel('Tags')
            ->formatValue(static fn ($value, Page $page): string => implode(',', $page->getTags()->toArray()))
        ;

        $fields = (array) parent::configureFields($pageName);

        return array_merge($fields, [$tags]);
    }

    /**
     * @param Page $entityInstance
     */
    #[\Override]
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setCategory(Category::PAGE);

        parent::persistEntity($entityManager, $entityInstance);

        $this->dispatchCache($entityInstance);
    }

    /**
     * @param Page $entityInstance
     */
    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);

        $this->dispatchCache($entityInstance);
    }
}
