<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Badge;

use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Model\Badge;
use App\Domain\Competition\Config\Type;
use App\Domain\File\Admin\Field\PhotoField;
use App\Domain\File\Form\PhotoFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

use function Symfony\Component\Translation\t;

final class BadgeCrudController extends AbstractCrudController
{
    public function __construct(protected readonly EntityRepository $entityRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Badge::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Listes des badges')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Badge $badge) => (string) $badge)
            ->setPageTitle(Crud::PAGE_EDIT, fn (Badge $badge) => sprintf('Edition le badge <b>%s</b>', $badge))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $name = TextField::new('name');
        $code = TextField::new('code');
        $official = BooleanField::new('official');
        $type = TextField::new('type');
        $level = NumberField::new('level');
        $image = PhotoField::new('image')
            ->setFormType(PhotoFormType::class);
        $conditions = ArrayField::new('conditions');

        $competitionType = ChoiceField::new('competitionType')
            ->setLabel('Type de competition')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Type::class,
                'choice_label' => fn (Type $choice) => t($choice->value, domain: 'competition'),
                'choices' => Type::cases(),
            ])
            ->formatValue(fn ($value, ?Badge $entity) => !$value || !$entity || !$entity->getCompetitionType() ? '' : t($entity->getCompetitionType()->value, domain: 'competition'))
        ;

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            if ($this->isGranted(Archer::ROLE_DEVELOPER)) {
                yield $id;
            }
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield $createdAt;
        }

        yield $name;
        yield $code;
        yield $official;
        yield $type;
        yield $competitionType;
        yield $level;
        yield $image;
        yield $conditions;
    }
}
