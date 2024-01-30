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

class LicenseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return License::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des type de licence')
            ->setPageTitle('new', 'Ajouter une licence')
            ->setPageTitle('detail', fn (License $license) => (string) $license)
            ->setPageTitle('edit', fn (License $license) => sprintf('Edition de la licence <b>%s</b>', $license))
        ;
    }

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
