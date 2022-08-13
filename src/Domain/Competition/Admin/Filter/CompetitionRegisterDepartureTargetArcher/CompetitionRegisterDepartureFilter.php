<?php

declare(strict_types=1);

namespace App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher;

use App\Domain\Competition\Model\CompetitionRegisterDeparture;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\EntityFilterType;

class CompetitionRegisterDepartureFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(EntityFilterType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'value_type_options' => [
                    'class' => CompetitionRegisterDeparture::class,
                ],
            ])
        ;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (/** @var ?CompetitionRegisterDeparture $departure */ $departure = $filterDataDto->getValue()) {
            $queryBuilder
                ->join(sprintf('%s.%s', $filterDataDto->getEntityAlias(), 'target'), 'target')
                ->join('target.departure', 'departure')
                ->andWhere('departure.id = :id')
                ->setParameter('id', $departure->getId(), 'uuid');
        }
    }
}
