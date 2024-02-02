<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\License;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class LicenseCrudController extends AbstractCrudController
{
    #[\Override]
    public static function getEntityFqcn(): string
    {
        return License::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des type de licence')
            ->setPageTitle('new', 'Ajouter une licence')
            ->setPageTitle('detail', static fn(License $license): string => (string) $license)
            ->setPageTitle('edit', static fn(License $license): string => sprintf('Edition de la licence <b>%s</b>', $license))
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $title = TextField::new('title')
            ->setLabel('Titre');
        $price = MoneyField::new('price')
            ->setCurrency('EUR')
            ->setLabel('Prix');
        $type = TextField::new('type')
            ->setLabel('Type');
        $description = TextareaField::new('description')
            ->setLabel('Description');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $price, $type];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $price, $type, $description];
        }

        return [$title, $price, $type, $description];
    }
}
