<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\CompetitionRegister;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Form\CompetitionRegisterDepartureForm;
use App\Domain\Competition\Manager\CompetitionRegisterManager;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\File\Admin\Field\DocumentField;
use App\Http\Admin\Controller\Cms\AbstractPageCrudController;
use App\Http\Landing\Controller\CompetitionRegister\Registration\IndexController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

use Symfony\Component\Translation\TranslatableMessage;

final class CompetitionRegisterCrudController extends AbstractCrudController
{
    public function __construct(
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private AdminUrlGenerator $adminUrlGenerator,
        readonly private CompetitionRegisterManager $competitionRegisterManager,
        readonly private EntityManagerInterface $em,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return CompetitionRegister::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', "Formulaire d'inscription au concours de Caen")
            ->setPageTitle('new', "Ajouter un formulaire d'inscription")
            ->setPageTitle('detail', static fn (CompetitionRegister $competitionRegister): string => (string) $competitionRegister)
            ->setPageTitle('edit', static fn (CompetitionRegister $competitionRegister): string => sprintf("Edition du formulaire l'inscription <b>%s</b>", $competitionRegister))
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $publicLink = Action::new('public-link')
            ->setLabel('Lien public')
            ->linkToUrl(function (CompetitionRegister $competitionRegister): string {
                return $this->urlGenerator->generate(
                    IndexController::ROUTE,
                    ['slug' => $competitionRegister->getSlug()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            })
        ;

        $registerList = Action::new('register-list')
            ->setLabel('Voir les inscrits')
            ->linkToUrl(function (CompetitionRegister $competitionRegister): string {
                return $this->adminUrlGenerator
                    ->setController(CompetitionRegisterArcherCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters', [
                        'competitionRegister' => [
                            'comparison' => '=',
                            'value' => $competitionRegister->getId(),
                        ],
                    ])
                    ->generateUrl()
                ;
            })
        ;

        $generateActuality = Action::new('generate-actuality')
            ->setLabel('Générer une actualité')
            ->linkToCrudAction('generateActuality')
        ;

        return $actions
            ->update(Crud::PAGE_INDEX, 'new', static fn (Action $action): Action => $action->setLabel("Créer un formulaire d'inscription"))
            ->add(Crud::PAGE_INDEX, $publicLink)
            ->add(Crud::PAGE_INDEX, $registerList)
            ->add(Crud::PAGE_INDEX, $generateActuality)
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')
            ->setPermission(Archer::ROLE_DEVELOPER);

        $type = ChoiceField::new('types', 'Types de concours')
            ->allowMultipleChoices()
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Type::class,
                'choice_label' => static fn (Type $choice): TranslatableMessage => t($choice->value, domain: 'competition'),
                'choices' => Type::cases(),
            ])
            ->formatValue(static function ($value, ?CompetitionRegister $entity): string {
                if (!$value || !$entity instanceof CompetitionRegister || !$entity->getTypes()) {
                    return '';
                }

                return implode(', ', array_map(static fn (Type $type): TranslatableMessage => t($type->value, domain: 'competition'), $entity->getTypes()));
            })
        ;

        $dateStart = DateField::new('dateStart', 'Date de début');
        $dateEnd = DateField::new('dateEnd', 'Date de fin');

        $departures = CollectionField::new('departures', 'Départs')
            ->setEntryType(CompetitionRegisterDepartureForm::class)
        ;

        $mandate = DocumentField::new('mandate', 'Mandat')
            ->setRequired(false)
        ;

        $autoCreateActuality = BooleanField::new('autoCreateActuality')
            ->setLabel('Créer automatiquement l\'article ?')
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('attr', ['checked' => true])
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $dateStart, $dateEnd];
        }

        return [$type, $dateStart, $dateEnd, $departures, $mandate, $autoCreateActuality];
    }

    /**
     * @param CompetitionRegister $entityInstance
     */
    #[\Override]
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->autoSetSlug();

        parent::persistEntity($entityManager, $entityInstance);

        $competitionRegisterUrl = $this->urlGenerator->generate(
            IndexController::ROUTE,
            ['slug' => $entityInstance->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->addFlash(
            'success',
            'Formation d\'inscription au concours créer, lien public <a href="'.$competitionRegisterUrl.'">ici</a>'
        );

        $context = $this->getContext();
        $competitionData = $context?->getRequest()->request->all()['CompetitionRegister'];

        if (\is_array($competitionData) && isset($competitionData['autoCreateActuality'])) {
            $actuality = $this->competitionRegisterManager->createActuality($entityInstance);

            parent::persistEntity($entityManager, $actuality);

            $actualityAdminUrl = $this->adminUrlGenerator
                ->setController(AbstractPageCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId($actuality->getId())
                ->generateUrl()
            ;

            $this->addFlash(
                'success',
                'Actualité créer, modifiable <a href="'.$actualityAdminUrl.'">ici</a>'
            );
        }
    }

    public function generateActuality(AdminContext $context): Response
    {
        $actuality = $this->competitionRegisterManager->createActuality($context->getEntity()->getInstance());

        $this->em->persist($actuality);
        $this->em->flush();

        $actualityAdminUrl = $this->adminUrlGenerator
            ->setController(AbstractPageCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($actuality->getId())
            ->generateUrl()
        ;

        return $this->redirect($actualityAdminUrl);
    }
}
