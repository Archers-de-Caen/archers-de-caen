<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Badge;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

final class ResultBadgeProgressArrowCrudController extends ResultBadgeCrudController
{
    public function __construct(EntityRepository $entityRepository)
    {
        $this->badgeType = 'progress_arrow';

        parent::__construct($entityRepository);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des flèches de progression')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une flèche de progression')
        ;
    }
}
