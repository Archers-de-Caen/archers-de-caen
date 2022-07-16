<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Form\ManagementTeam\ManagementTeamForm;
use App\Domain\Cms\Model\Data;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\FormBuilderInterface;

class DataCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Data::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des données structurées du site')
            ->setPageTitle('new', 'Ajouter une donnée structuré au site')
            ->setPageTitle('detail', fn (Data $data) => (string) $data)
            ->setPageTitle('edit', fn (Data $data) => sprintf('Edition d\'une donnée structuré <b>%s</b>', $data))
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->setPermission(Action::NEW, Archer::ROLE_DEVELOPER);
    }

    public function configureFields(string $pageName): iterable
    {
        $createdAt = DateTimeField::new('createdAt', 'Date de création');

        $code = TextField::new('code', 'Code');
        $description = TextField::new('description', 'Description');

        $content = CollectionField::new('content', false);
        $formType = TextField::new('formType', 'Formulaire');

        if (Crud::PAGE_EDIT === $pageName) {
            return [$content];
        }

        if (Crud::PAGE_NEW === $pageName) {
            return [$code, $description, $formType];
        }

        return [$description, $createdAt];
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        /** @var Data $data */
        $data = $entityDto->getInstance();

        if ($data->getFormType()) {
            $entityDto
                ->getFields()
                ?->getByProperty('content')
                ?->setFormTypeOption('entry_type', ManagementTeamForm::class);
        }

        return parent::createEditFormBuilder($entityDto, $formOptions, $context);
    }
}
