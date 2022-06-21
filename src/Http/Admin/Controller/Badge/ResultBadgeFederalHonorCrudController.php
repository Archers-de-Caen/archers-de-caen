<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Badge;

use App\Domain\Result\Model\ResultBadge;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

final class ResultBadgeFederalHonorCrudController extends ResultBadgeCrudController
{
    public function __construct(EntityRepository $entityRepository)
    {
        $this->badgeType = 'competition';

        parent::__construct($entityRepository);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des distinctions fédérale')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une distinction fédérale')
        ;
    }
}
