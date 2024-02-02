<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\CompetitionRegister;

use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterConfigDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Translation\TranslatableMessage;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher\CompetitionRegisterDepartureFilter;
use App\Domain\Competition\Admin\Filter\CompetitionRegisterDepartureTargetArcher\CompetitionRegisterFilter;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Http\Landing\Controller\IndexController;
use Doctrine\ORM\EntityManagerInterface;
use Doskyft\CsvHelper\ColumnDefinition;
use Doskyft\CsvHelper\Csv;
use Doskyft\CsvHelper\Exception\NotCorrectColumnsException;
use Doskyft\CsvHelper\Types;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

class CompetitionRegisterArcherCrudController extends AbstractCrudController
{
    public function __construct(
        readonly private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return CompetitionRegisterDepartureTargetArcher::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des inscrits au concours de Caen')
            ->setPageTitle(Crud::PAGE_DETAIL, static fn(CompetitionRegisterDepartureTargetArcher $crdta): string => (string) $crdta)
            ->setPageTitle(Crud::PAGE_EDIT, static fn(CompetitionRegisterDepartureTargetArcher $crdta): string => sprintf("Modification de l'inscription <b>%s</b>", $crdta))
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $export = Action::new('export')
            ->createAsGlobalAction()
            ->linkToCrudAction('export')
        ;

        $import = Action::new('import')
            ->createAsGlobalAction()
            ->linkToCrudAction('import')
            ->setHtmlAttributes([
                'data-action-name' => 'batchImport',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modal-import-action',
                'data-bs-csv-model-href' => '/build/documents/exemple-import-competition-registration.csv',
                'data-bs-form-action-href' => $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('import')
                    ->generateUrl(),
            ])
        ;

        return $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, $export)
            ->add(Crud::PAGE_INDEX, $import)
        ;
    }

    #[\Override]
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

    #[\Override]
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
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Gender::class,
                'choice_label' => static fn(Gender $choice): TranslatableMessage => t($choice->value, domain: 'archer'),
                'choices' => Gender::cases(),
            ])
            ->formatValue(static fn($value, ?CompetitionRegisterDepartureTargetArcher $entity): TranslatableMessage|string => !$value || !$entity instanceof CompetitionRegisterDepartureTargetArcher || !$entity->getGender() instanceof Gender ? '' : t($entity->getGender()->value, domain: 'archer'))
        ;

        $category = ChoiceField::new('category')
            ->setLabel('Catégorie')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Category::class,
                'choice_label' => static fn(Category $choice): TranslatableMessage => t($choice->value, domain: 'archer'),
                'choices' => Category::cases(),
            ])
            ->formatValue(static fn($value, ?CompetitionRegisterDepartureTargetArcher $entity): ?TranslatableMessage => $entity?->getCategory()?->value ? t($entity->getCategory()->value, domain: 'archer') : null)
        ;

        $weapon = ChoiceField::new('weapon')
            ->setLabel('Arme')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Weapon::class,
                'choice_label' => static fn(Weapon $choice): TranslatableMessage => t($choice->value, domain: 'archer'),
                'choices' => Weapon::cases(),
            ])
            ->formatValue(static fn($value, ?CompetitionRegisterDepartureTargetArcher $entity): TranslatableMessage|string => !$value || !$entity instanceof CompetitionRegisterDepartureTargetArcher || !$entity->getWeapon() instanceof Weapon ? '' : t($entity->getWeapon()->value, domain: 'archer'))
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
        if (!$filterConfig instanceof FilterConfigDto) {
            return $this->redirectToRoute(IndexController::ROUTE);
        }

        $filters = $filterFactory->create($filterConfig, $fields, $context->getEntity());

        $search = $context->getSearch();
        if (!$search instanceof SearchDto) {
            return $this->redirectToRoute(IndexController::ROUTE);
        }

        $queryBuilder = $this->createIndexQueryBuilder($search, $context->getEntity(), $fields, $filters);

        /* @phpstan-ignore-next-line */
        $data = array_map(static function (CompetitionRegisterDepartureTargetArcher $registration): string {
            return implode(',', [
                'date_de_creation' => $registration->getCreatedAt()?->format(\DateTimeInterface::RFC822),
                'licence' => $registration->getLicenseNumber(),
                'prenom' => $registration->getFirstName(),
                'nom' => $registration->getLastName(),
                'email' => $registration->getEmail(),
                'phone' => $registration->getPhone(),
                'genre' => $registration->getGender()?->value ? t($registration->getGender()->value, domain: 'archer') : '',
                'categorie' => $registration->getCategory()?->value ? t($registration->getCategory()->value, domain: 'archer') : '',
                'arme' => $registration->getWeapon()?->value ? t($registration->getWeapon()->value, domain: 'archer') : '',
                'club' => $registration->getClub(),
                'fauteuil_roulant' => $registration->getWheelchair() ? 'Oui' : 'Non',
                'premiere_annee' => $registration->getFirstYear() ? 'Oui' : 'Non',
                'info_complementaire' => $registration->getAdditionalInformation(),
                'cible' => $registration->getTarget(),
                'depart' => $registration->getTarget()?->getDeparture(),
                'position' => $registration->getPosition(),
            ]);
        }, (array) $queryBuilder->getQuery()->getResult());

        $response = new Response(implode("\n", $data));
        $dispositionHeader = $response->headers->makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'liste_des_inscrits.csv'
        );
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');

        return $response;
    }

    public function import(AdminContext $context, EntityManagerInterface $em): Response
    {
        $request = $context->getRequest();

        $returnUrl = $context->getReferrer();
        if (!$returnUrl) {
            $returnUrl = $this->adminUrlGenerator
                ->setAction(Action::INDEX)
                ->setController(CompetitionRegisterCrudController::class)
                ->generateUrl()
            ;
        }

        if (!$request->files->has('import')) {
            $this->addFlash('danger', 'Vous devez fournir un fichier !');

            return $this->redirect($returnUrl);
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('import');

        /** @var CompetitionRegister[] $competitionRegisters */
        $competitionRegisters = $em->getRepository(CompetitionRegister::class)
            ->createQueryBuilder('cr')
            ->select('cr')
            ->addSelect('departure')
            ->addSelect('target')
            ->leftJoin('cr.departures', 'departure')
            ->leftJoin('departure.targets', 'target')
            ->getQuery()
            ->getResult()
        ;

        $csv = new Csv();
        $falseValues = [false, 'false', 'no', 'No', 0, '0', null, 'null', 'Non', 'non'];
        $csv
            ->setColumnSeparator(',')
            ->setColumns([
                ColumnDefinition::new('licence'),
                ColumnDefinition::new('prenom'),
                ColumnDefinition::new('nom'),
                ColumnDefinition::new('email'),
                ColumnDefinition::new('phone'),
                ColumnDefinition::new('genre', Types::ENUM)
                    ->setConverterOptions([
                        'enum' => Gender::class,
                        'internalConvertFunction' => 'createFromString',
                    ]),
                ColumnDefinition::new('categorie', Types::ENUM)
                    ->setConverterOptions([
                        'enum' => Category::class,
                        'internalConvertFunction' => 'createFromString',
                    ]),
                ColumnDefinition::new('arme', Types::ENUM)
                    ->setConverterOptions([
                        'enum' => Weapon::class,
                        'internalConvertFunction' => 'createFromString',
                    ]),
                ColumnDefinition::new('club'),
                ColumnDefinition::new('fauteuil_roulant', Types::BOOLEAN)
                    ->setConverterOptions([
                        'falseValues' => $falseValues,
                    ]),
                ColumnDefinition::new('premiere_annee', Types::BOOLEAN)
                    ->setConverterOptions([
                        'falseValues' => $falseValues,
                    ]),
                ColumnDefinition::new('info_complementaire'),
                ColumnDefinition::new('cible'),
                ColumnDefinition::new('depart'),
                ColumnDefinition::new('concours', Types::ENTITY)
                    ->setConverterOptions([
                        'find' => static function (string $value) use ($competitionRegisters) {
                            foreach ($competitionRegisters as $competitionRegister) {
                                if ($competitionRegister->__toString() === $value) {
                                    return $competitionRegister;
                                }
                            }

                            return null;
                        },
                    ]),
            ])
        ;

        try {
            $results = $csv->readFromString($file->getContent());
        } catch (NotCorrectColumnsException $notCorrectColumnsException) {
            $this->addFlash('danger', 'Le CSV ne respecte pas le bon format: '.$notCorrectColumnsException->getMessage());

            return $this->redirect($returnUrl);
        }

        $registrations = [];

        foreach ($results as $result) {
            /** @var CompetitionRegister $competition */
            $competition = $result['concours'];
            $registration = null;

            foreach ($competition->getDepartures() as $departure) {
                if ($departure->__toString() === $result['depart']) {
                    foreach ($departure->getTargets() as $target) {
                        if ($target->__toString() === $result['cible']) {
                            $registration = (new CompetitionRegisterDepartureTargetArcher())
                                ->setLicenseNumber($result['licence'])
                                ->setFirstName($result['prenom'])
                                ->setLastName($result['nom'])
                                ->setGender($result['genre'])
                                ->setEmail($result['email'])
                                ->setPhone($result['phone'])
                                ->setCategory($result['categorie'])
                                ->setWeapon($result['arme'])
                                ->setClub($result['club'])
                                ->setWheelchair($result['fauteuil_roulant'])
                                ->setFirstYear($result['premiere_annee'])
                                ->setAdditionalInformation($result['info_complementaire'])
                            ;

                            $target->addArcher($registration);

                            $registrations[] = $registration;

                            break;
                        }
                    }

                    break;
                }
            }

            if (!$registration instanceof CompetitionRegisterDepartureTargetArcher) {
                $this->addFlash(
                    'danger',
                    'Impossible d\'importé la ligne avec la licence: "'.($result['licence'] ?? '').'"'
                );

                return $this->redirect($returnUrl);
            }
        }

        $em->flush();

        $this->addFlash('success', 'Inscription importé ! '.\count($registrations).' inscriptions ont étaient importé');

        return $this->redirect($returnUrl);
    }
}
