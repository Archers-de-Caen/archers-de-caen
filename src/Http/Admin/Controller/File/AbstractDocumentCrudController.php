<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\File;

use App\Domain\File\Model\Document;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

abstract class AbstractDocumentCrudController extends AbstractCrudController
{
    public function __construct(
        protected readonly EntityRepository $entityRepository,
        private readonly UploaderHelper $uploaderHelper,
        private readonly string $baseHost
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Document::class;
    }

    #[\Override]
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
            ->formatValue(fn (string $value, Document $document): string => $this->baseHost.$this->uploaderHelper->asset($document, 'documentFile'))
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $link, $createdAt];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $createdAt];
        }

        return [$title, $upload];
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $display = Action::new('display')
            ->setLabel('Afficher')
            ->linkToUrl(fn (Document $document): ?string => $this->uploaderHelper->asset($document, 'documentFile'));

        return $actions
            ->add(Crud::PAGE_INDEX, $display)
        ;
    }
}
