<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\File;

use App\Domain\File\Config\DocumentType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

final class DocumentCrudController extends AbstractDocumentCrudController
{
    #[\Override]
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.type = :documentType')
            ->setParameter('documentType', DocumentType::OTHER->value)
        ;
    }
}
