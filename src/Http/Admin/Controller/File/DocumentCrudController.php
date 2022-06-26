<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\File;

use App\Domain\File\Config\DocumentType;
use App\Domain\File\Model\Document;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichFileType;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(protected readonly EntityRepository $entityRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.type = :documentType')
            ->setParameter('documentType', DocumentType::OTHER->value)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $title = TextField::new('displayText')
            ->setLabel('Titre');

        $upload = TextareaField::new('documentFile')
            ->setLabel('Fichier')
            ->setFormType(VichFileType::class);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $createdAt];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $createdAt];
        }

        return [$title, $upload];
    }
}
