<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Badge;

use App\Domain\Badge\Model\Badge;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

class ResultBadgeFederalHonorCrudController extends ResultBadgeCrudController
{
    public function __construct(EntityRepository $entityRepository)
    {
        $this->badgeType = Badge::COMPETITION;

        parent::__construct($entityRepository);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des distinctions fédérale')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une distinction fédérale')
        ;
    }
}
