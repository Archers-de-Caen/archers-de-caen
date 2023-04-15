<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Badge;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Model\Badge;
use App\Domain\Result\Model\ResultBadge;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

use function Symfony\Component\Translation\t;

abstract class ResultBadgeCrudController extends AbstractCrudController
{
    protected string $badgeType = '';

    public function __construct(protected readonly EntityRepository $entityRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ResultBadge::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_DETAIL, fn (ResultBadge $resultBadge) => (string) $resultBadge)
            ->setPageTitle(Crud::PAGE_EDIT, fn (ResultBadge $resultBadge) => sprintf('Edition du résultat <b>%s</b>', $resultBadge))
            ->setDefaultSort(['completionDate' => 'DESC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action->setIcon('fa fa-bullseye')
                ->setLabel('Ajouter un nouveau résultat')
            );
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        return $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->join('entity.badge', 'badge')
            ->andWhere(sprintf("badge.type = '%s'", $this->badgeType))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $badge = AssociationField::new('badge')
            ->setLabel(match ($this->badgeType) {
                Badge::COMPETITION => 'Distinction fédérale',
                Badge::PROGRESS_ARROW => 'Flèche de progression',
                default => 'wut ?'
            })
            ->setQueryBuilder(
                fn (QueryBuilder $queryBuilder) => $queryBuilder
                        ->where(sprintf("entity.type = '%s'", $this->badgeType))
            );

        $archer = AssociationField::new('archer');
        $score = IntegerField::new('score');
        $completionDate = DateField::new('completionDate')
            ->setLabel('Date d\'obtention')
        ;

        $weapon = ChoiceField::new('weapon')
            ->setLabel('Arme')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Weapon::class,
                'choice_label' => fn (Weapon $choice) => t($choice->value, domain: 'archer'),
                'choices' => Weapon::cases(),
            ])
            ->formatValue(fn ($value, ?ResultBadge $entity) => !$value || !$entity || !$entity->getWeapon() ? '' : t($entity->getWeapon()->value, domain: 'archer'))
        ;

        $category = ChoiceField::new('category')
            ->setLabel('Catégorie')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Category::class,
                'choice_label' => fn (Category $choice) => t($choice->value, domain: 'archer'),
                'choices' => Category::cases(),
            ])
            ->formatValue(fn ($value, ?ResultBadge $entity) => !$value || !$entity || !$entity->getCategory() ? '' : t($entity->getCategory()->value, domain: 'archer'))
        ;

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            if ($this->isGranted(Archer::ROLE_DEVELOPER)) {
                yield $id;
            }
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield $createdAt;
        }

        yield $badge;
        yield $archer;
        yield $score;
        yield $completionDate;
        yield $weapon;
        yield $category;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $weapon = ChoiceFilter::new('weapon')
            ->setLabel('Arme')
            ->setChoices(Weapon::cases())
        ;

        $archer = EntityFilter::new('archer');
        $badge = EntityFilter::new('badge');

        return $filters
            ->add($archer)
            ->add($weapon)
            ->add($badge)
        ;
    }
}
