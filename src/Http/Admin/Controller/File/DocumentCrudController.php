<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\File;

use App\Domain\File\Config\DocumentType;
use App\Domain\File\Model\Document;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class DocumentCrudController extends AbstractCrudController
{
    public function __construct(
        protected readonly EntityRepository $entityRepository,
        private readonly UploaderHelper $uploaderHelper,
        private readonly string $baseHost
    ) {
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

        $link = UrlField::new('documentName')
            ->setLabel('Fichier')
            ->formatValue(function (string $value, Document $document) {
                return $this->baseHost.$this->uploaderHelper->asset($document, 'documentFile');
            })
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $link, $createdAt];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $createdAt];
        }

        return [$title, $upload];
    }

    public function configureActions(Actions $actions): Actions
    {
        $display = Action::new('display')
            ->setLabel('Afficher')
            ->linkToUrl(fn (Document $document) => $this->uploaderHelper->asset($document, 'documentFile'));

        return $actions
            ->add(Crud::PAGE_INDEX, $display)
        ;
    }
}
