<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\CompetitionRegister;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher\CompetitionRegisterDepartureFilter;
use App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher\CompetitionRegisterFilter;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Http\Landing\Controller\DefaultController;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompetitionRegisterArcherCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompetitionRegisterDepartureTargetArcher::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des inscrits au concours de Caen')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (CompetitionRegisterDepartureTargetArcher $crdta) => (string) $crdta)
            ->setPageTitle(Crud::PAGE_EDIT, fn (CompetitionRegisterDepartureTargetArcher $crdta) => sprintf("Modification de l'inscription <b>%s</b>", $crdta))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('export')
            ->createAsGlobalAction()
            ->linkToCrudAction('export')
        ;

        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, $export)
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

        $weapon = ChoiceField::new('weapon')
            ->setLabel('Arme')
            ->setChoices(Crud::PAGE_EDIT === $pageName ? Weapon::toChoicesWithEnumValue() : Weapon::toChoices())
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
            yield $weapon;
            yield $club;
            yield $wheelchair;
            yield $firstYear;
            yield $additionalInformation;
            yield $target;
            yield $departure;
        }

        yield $position;
    }

    public function export(AdminContext $context): Response
    {
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));

        /** @var FilterFactory $filterFactory */
        $filterFactory = $this->container->get(FilterFactory::class);
        $filterConfig = $context->getCrud()?->getFiltersConfig();
        if (!$filterConfig) {
            return $this->redirectToRoute(DefaultController::ROUTE_LANDING_INDEX);
        }

        $filters = $filterFactory->create($filterConfig, $fields, $context->getEntity());

        $search = $context->getSearch();
        if (!$search) {
            return $this->redirectToRoute(DefaultController::ROUTE_LANDING_INDEX);
        }
        $queryBuilder = $this->createIndexQueryBuilder($search, $context->getEntity(), $fields, $filters);

        $data = array_map(static fn (CompetitionRegisterDepartureTargetArcher $registration): string => implode(',', [
            'date_de_creation' => $registration->getCreatedAt()?->format(DateTimeInterface::RFC822),
            'licence' => $registration->getLicenseNumber(),
            'prenom' => $registration->getFirstName(),
            'nom' => $registration->getLastName(),
            'email' => $registration->getEmail(),
            'phone' => $registration->getPhone(),
            'genre' => $registration->getGender()?->toString(),
            'categorie' => $registration->getCategory()?->toString(),
            'arme' => $registration->getWeapon()?->toString(),
            'club' => $registration->getClub(),
            'fauteuil_roulant' => $registration->getWheelchair() ? 'Oui' : 'Non',
            'premiere_annee' => $registration->getFirstYear() ? 'Oui' : 'Non',
            'info_complementaire' => $registration->getAdditionalInformation(),
            'cible' => $registration->getTarget(),
            'depart' => $registration->getTarget()?->getDeparture(),
            'position' => $registration->getPosition(),
        ]), (array) $queryBuilder->getQuery()->getResult());

        $response = new Response(implode("\n", $data));
        $dispositionHeader = $response->headers->makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'liste_des_inscrits.csv'
        );
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');

        return $response;
    }
}
