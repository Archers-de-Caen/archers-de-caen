<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Cms;

use App\Domain\Cms\Admin\Field\CKEditorField;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\File\Admin\Field\PhotoField;
use App\Domain\File\Form\PhotoFormType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AbstractPageCrudController extends AbstractCrudController
{
    public function __construct(
        readonly protected UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->select('entity')
            ->addSelect('tags')
            ->addSelect('image')

            ->leftJoin('entity.tags', 'tags')
            ->leftJoin('entity.image', 'image')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setHelp(Crud::PAGE_NEW, 'Le rendu final peut-être différent de l\'éditeur')
            ->setHelp(Crud::PAGE_EDIT, 'Le rendu final peut-être différent de l\'éditeur')

            ->setPageTitle(Crud::PAGE_DETAIL, fn (Page $page) => (string) $page)

            ->addFormTheme('form/ckeditor.html.twig')
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $title = TextField::new('title')
            ->setLabel('Titre');

        $content = CKEditorField::new('content');

        $status = ChoiceField::new('status')
            ->setLabel('Statut')
            ->setChoices(
                array_combine(
                    array_map(static fn (Status $status) => $status->toString(), Status::cases()),
                    array_map(static fn (Status $status) => $status->value, Status::cases())
                )
            ); // TODO: provisoire le temps que le bundle EasyAdmin ce met a jours

        $image = PhotoField::new('image')
            ->setLabel('Image')
            ->setFormType(PhotoFormType::class);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $status, $image, $createdAt];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $status, $image, $content, $createdAt];
        }

        $status = ChoiceField::new('status')
            ->setLabel('Statut')
            ->setChoices(Status::toChoicesWithEnumValue());

        return [$title, $status, $image, $content];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('category')->setChoices(Category::toChoices()))
            ->add(ChoiceFilter::new('status')->setChoices(Status::toChoices()))
        ;
    }
}
