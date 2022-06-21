<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Cms\Model\Photo;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PhotoCrudController extends AbstractCrudController
{
    public function __construct(private readonly EntityRepository $entityRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.galleryMainPhoto', 'galleryMainPhoto')
            ->andWhere('entity.gallery IS NULL')
            ->andWhere('galleryMainPhoto.id IS NULL');
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $title = TextField::new('imageName')
            ->setLabel('Titre');

        $upload = TextareaField::new('imageFile')
            ->setLabel('Fichier')
            ->setFormType(VichImageType::class);

        $image = ImageField::new('imageFile')
            ->setLabel('Fichier');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $upload, $createdAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $image, $createdAt];
        } else {
            return [$upload];
        }
    }
}
