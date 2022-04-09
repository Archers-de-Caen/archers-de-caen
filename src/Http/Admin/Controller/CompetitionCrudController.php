<?php

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Model\Competition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompetitionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Competition::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $location = TextField::new('location')
            ->setLabel('Lieu');
        $dateStart = DateTimeField::new('dateStart')
            ->setLabel('Début');
        $dateEnd = DateTimeField::new('dateEnd')
            ->setLabel('Fin');
        $type = TextField::new('type')
            ->setLabel('Type');
        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            if ($this->isGranted(Archer::ROLE_DEVELOPER)) {
                yield $id;
            }

            yield $createdAt;
        }

        yield $location;
        yield $dateStart;
        yield $dateEnd;
        yield $type;
    }
}
