<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Model\CompetitionRegister;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class CompetitionRegisterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompetitionRegister::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Inscription au concours de Caen')
            ->setPageTitle('new', 'Ajouter un concours de Caen')
            ->setPageTitle('detail', fn (Archer $archer) => (string) $archer)
            ->setPageTitle('edit', fn (Archer $archer) => sprintf('Edition de l\'inscription <b>%s</b>', $archer))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
        ];
    }
}
