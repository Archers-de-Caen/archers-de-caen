<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Service\CompetitionService;
use App\Domain\Newsletter\NewsletterType;
use App\Domain\Result\Form\ResultCompetitionForm;
use App\Domain\Result\Form\ResultTeamForm;
use App\Domain\Result\Manager\ResultCompetitionManager;
use App\Domain\Result\Model\ResultCompetition;
use App\Http\Admin\Controller\Cms\AbstractPageCrudController;
use App\Http\Landing\Controller\Results\CompetitionController;
use App\Infrastructure\Mailing\CompetitionResultsNewsletterMessage;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Uuid;

final class CompetitionCrudController extends AbstractCrudController
{
    public function __construct(
        readonly private ResultCompetitionManager $resultCompetitionManager,
        readonly private CompetitionService $competitionManager,
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private AdminUrlGenerator $adminUrlGenerator,
        readonly private MessageBusInterface $messageBus,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Competition::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['dateStart' => 'DESC'])
            ->overrideTemplate('crud/index', '@EasyAdmin\page\competition\index.html.twig')
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $publicLink = Action::new('publicLink', 'Page public')
            ->linkToUrl(
                function (Competition $competition): string {
                    return $this->urlGenerator->generate(
                        CompetitionController::ROUTE,
                        ['slug' => $competition->getSlug()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }
            )
        ;

        $copyIframe = Action::new('iframe')
            ->linkToUrl(
                function (Competition $competition): string {
                    return $this->urlGenerator->generate(
                        name: CompetitionController::ROUTE,
                        parameters: ['slug' => $competition->getSlug(), 'iframe' => true],
                        referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }
            )
            ->setHtmlAttributes([
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modal-copy-string',
            ])
        ;

        $sendNewsletter = Action::new('send-newsletter', 'Envoyer la newsletter')
            ->linkToCrudAction('sendNewsletter')
            ->displayIf(static fn (Competition $competition): bool => $competition->getResults()->count() > 0)
            ->setHtmlAttributes([
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modal-send-newsletter-confirm',
            ])
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $publicLink)
            ->add(Crud::PAGE_INDEX, $copyIframe)
            ->add(Crud::PAGE_INDEX, $sendNewsletter)
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $location = TextField::new('location')
            ->setLabel('Lieu');
        $dateStart = DateField::new('dateStart')
            ->setLabel('Début')
            ->setColumns('col-3');
        $dateEnd = DateField::new('dateEnd')
            ->setLabel('Fin')
            ->setColumns('col-3');

        $type = ChoiceField::new('type')
            ->setLabel('Type')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Type::class,
                'choice_label' => static fn (Type $choice): TranslatableMessage => t($choice->value, domain: 'competition'),
                'choices' => Type::cases(),
            ])
            ->formatValue(static fn ($value, ?Competition $entity): TranslatableMessage|string => !$value || !$entity instanceof Competition || !$entity->getType() instanceof Type ? '' : t($entity->getType()->value, domain: 'competition'))
        ;

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $results = CollectionField::new('results')
            ->setEntryType(ResultCompetitionForm::class)
            ->setLabel('Résultat individuel');

        $resultsTeams = CollectionField::new('resultsTeams')
            ->setEntryType(ResultTeamForm::class)
            ->setLabel('Résultat d\'équipe');

        $autoCreateActuality = BooleanField::new('autoCreateActuality')
            ->setLabel('Créer automatiquement l\'article ?')
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('attr', ['checked' => true]);

        if ((Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) && $this->isGranted(Archer::ROLE_DEVELOPER)) {
            yield $id;
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            yield $createdAt;
        }

        yield $location;
        yield $type;
        yield $dateStart;
        yield $dateEnd;

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield $results;
            yield $resultsTeams;
        }

        if (Crud::PAGE_NEW === $pageName) {
            yield $autoCreateActuality;
        }
    }

    /**
     * @param Competition $entityInstance
     */
    #[\Override]
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        foreach ($entityInstance->getResults() as $result) {
            $this->resultCompetitionManager->awardingBadges($result);
            $this->resultCompetitionManager->awardingRecord($result);
        }

        $entityInstance->autoSetSlug();

        parent::persistEntity($entityManager, $entityInstance);

        $competitionUrl = $this->urlGenerator->generate(CompetitionController::ROUTE, [
            'slug' => $entityInstance->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addFlash('success', 'Compétition créer, lien public <a href="'.$competitionUrl.'">ici</a>');

        $context = $this->getContext();
        $competitionData = $context?->getRequest()->request->all()['Competition'];

        if (\is_array($competitionData) && isset($competitionData['autoCreateActuality'])) {
            $actuality = $this->competitionManager->createActuality($entityInstance);

            parent::persistEntity($entityManager, $actuality);

            $actualityAdminUrl = $this->adminUrlGenerator
                ->setController(AbstractPageCrudController::class)
                ->setAction(Action::EDIT)
                ->setEntityId($actuality->getId())
                ->generateUrl()
            ;

            $this->addFlash('success', 'Actualité créer, modifiable <a href="'.$actualityAdminUrl.'">ici</a>');
        }
    }

    /**
     * @param Competition $entityInstance
     */
    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $resultsNotPersisted = $entityInstance->getResults()->filter(static fn (ResultCompetition $resultCompetition): bool => !$resultCompetition->getId());

        foreach ($resultsNotPersisted as $result) {
            $this->resultCompetitionManager->awardingBadges($result);
            $this->resultCompetitionManager->awardingRecord($result);
        }

        parent::updateEntity($entityManager, $entityInstance);

        $competitionUrl = $this->urlGenerator->generate(CompetitionController::ROUTE, [
            'slug' => $entityInstance->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addFlash('success', 'Compétition mise à jour, lien public <a href="'.$competitionUrl.'">ici</a>');
    }

    /**
     * @throws ExceptionInterface
     */
    public function sendNewsletter(AdminContext $context): RedirectResponse
    {
        /** @var Uuid $uuid */
        $uuid = $context->getEntity()->getInstance()?->getId();

        $message = new CompetitionResultsNewsletterMessage(
            competitionUuid: $uuid,
            type: NewsletterType::COMPETITION_RESULTS_NEW
        );

        $this->messageBus->dispatch($message);

        $this->addFlash('success', 'La newsletter a été envoyée');

        $referer = $context->getReferrer();

        if (null === $referer) {
            $referer = $this->adminUrlGenerator
                ->setController(__CLASS__)
                ->setAction(Action::INDEX)
                ->generateUrl();
        }

        return $this->redirect($referer);
    }
}
