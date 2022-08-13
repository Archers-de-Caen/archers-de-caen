<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\CompetitionRegister;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher\CompetitionRegisterDepartureFilter;
use App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher\CompetitionRegisterFilter;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class CompetitionRegisterArcherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompetitionRegisterDepartureTargetArcher::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des inscrits au concours de Caen')
            ->setPageTitle('new', 'Ajouter un inscrit')
            ->setPageTitle('detail', fn (CompetitionRegisterDepartureTargetArcher $crdta) => (string) $crdta)
            ->setPageTitle('edit', fn (CompetitionRegisterDepartureTargetArcher $crdta) => sprintf("Modification de l'inscription <b>%s</b>", $crdta))
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('licenseNumber'))
            ->add(TextFilter::new('firstName'))
            ->add(TextFilter::new('lastName'))
            ->add(TextFilter::new('phone'))
            ->add(TextFilter::new('email'))
            ->add(CompetitionRegisterDepartureFilter::new('departure', 'Départ'))
            ->add(CompetitionRegisterFilter::new('competitionRegister', 'Concours'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')
            ->setPermission(Archer::ROLE_DEVELOPER);

        $licenseNumber = TextField::new('licenseNumber')
            ->setLabel('Numéro de licence');

        $firstName = TextField::new('firstName')
            ->setLabel('Prénom');

        $lastName = TextField::new('lastName')
            ->setLabel('Nom');

        $phone = TextField::new('phone')
            ->setLabel('Téléphone');

        $email = EmailField::new('email');

        $createdAt = DateTimeField::new('createdAt')->setLabel('Date de création');

        $gender = ChoiceField::new('gender')
            ->setLabel('Genre')
            ->setChoices(Crud::PAGE_EDIT === $pageName ? Gender::toChoicesWithEnumValue() : Gender::toChoices())
        ;

        $category = ChoiceField::new('category')
            ->setLabel('Catégorie')
            ->setChoices(Crud::PAGE_EDIT === $pageName ? Category::toChoicesWithEnumValue() : Category::toChoices())
        ;

        $club = TextField::new('club')
            ->setLabel('Club')
        ;

        $wheelchair = BooleanField::new('wheelchair')
            ->setLabel('Fauteuil roulant')
        ;

        $firstYear = BooleanField::new('firstYear')
            ->setLabel('1ere année')
        ;

        $additionalInformation = TextField::new('additionalInformation')
            ->setLabel('Info. complémentaire')
        ;

        $target = AssociationField::new('target')
            ->setLabel('Cible')
        ;

        $departure = TextField::new('target.departure')
            ->setLabel('Départ')
            ->formatValue(static fn (?string $departure): ?string => $departure)
        ;

        $position = TextField::new('position')
            ->setLabel('Position')
        ;

        if (Crud::PAGE_EDIT !== $pageName) {
            yield $id;
            yield $createdAt;
            yield $licenseNumber;
            yield $firstName;
            yield $lastName;
            yield $email;
            yield $phone;
            yield $gender;
            yield $category;
            yield $club;
            yield $wheelchair;
            yield $firstYear;
            yield $additionalInformation;
            yield $target;
            yield $departure;
        }

        yield $position;
    }
}
