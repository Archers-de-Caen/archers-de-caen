<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\File;

use Doctrine\ORM\Query\Expr\Join;
use App\Domain\Cms\Model\Gallery;
use App\Domain\File\Model\Photo;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PhotoCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityRepository $entityRepository,
        private readonly UploaderHelper $uploaderHelper,
        private readonly string $baseHost
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['createdAt' => 'DESC']);
    }

    #[\Override]
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin(Gallery::class, 'g', Join::WITH, 'g.mainPhoto = entity')
            ->andWhere('entity.gallery IS NULL')
            ->andWhere('g IS NULL')
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $link = UrlField::new('imageName')
            ->setLabel('Lien')
            ->formatValue(fn (string $value, Photo $photo): string => $this->baseHost.$this->uploaderHelper->asset($photo))
        ;

        $upload = TextareaField::new('imageFile')
            ->setLabel('Fichier')
            ->setFormType(VichImageType::class)
            // ->setTemplatePath('@EasyAdmin/crud/field/photo.html.twig')
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $link, $createdAt];
        }

        return [$upload];
    }
}
