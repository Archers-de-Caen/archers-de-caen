<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Manager\CompetitionManager;
use App\Domain\Competition\Model\Competition;
use App\Domain\Result\Form\ResultCompetitionFormType;
use App\Domain\Result\Manager\ResultCompetitionManager;
use App\Domain\Result\Model\ResultCompetition;
use App\Http\Landing\Controller\CompetitionController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CompetitionCrudController extends AbstractCrudController
{
    public function __construct(
        readonly private ResultCompetitionManager $resultCompetitionManager,
        readonly private CompetitionManager $competitionManager,
        readonly private UrlGeneratorInterface $urlGenerator,
        readonly private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Competition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['dateStart' => 'DESC'])
        ;
    }

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
            ->setLabel('Type');

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            $type->setChoices(Type::toChoicesWithEnumValue());
        } else {
            $type->setChoices(
                array_combine(
                    array_map(static fn (Type $type) => $type->toString(), Type::cases()),
                    array_map(static fn (Type $type) => $type->value, Type::cases())
                )
            ); // TODO: provisoire le temps que le bundle EasyAdmin ce met a jours
        }

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $results = CollectionField::new('results')
            ->setEntryType(ResultCompetitionFormType::class);

        $autoCreateActuality = BooleanField::new('autoCreateActuality')
            ->setLabel('Créer automatiquement l\'article ?')
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('attr', ['checked' => true]);

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            if ($this->isGranted(Archer::ROLE_DEVELOPER)) {
                yield $id;
            }
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
        }

        if (Crud::PAGE_NEW === $pageName) {
            yield $autoCreateActuality;
        }
    }

    /**
     * @param Competition $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        foreach ($entityInstance->getResults() as $result) {
            $this->resultCompetitionManager->awardingBadges($result);
            $this->resultCompetitionManager->awardingRecord($result);
        }

        $entityInstance->autoSetSlug();

        parent::persistEntity($entityManager, $entityInstance);

        $competitionUrl = $this->urlGenerator->generate(CompetitionController::ROUTE_LANDING_RESULTS_COMPETITION, [
            'slug' => $entityInstance->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addFlash('success', 'Compétition créer, lien public <a href="'.$competitionUrl.'">ici</a>');

        $context = $this->getContext();
        $competitionData = $context?->getRequest()->request->all()['Competition'];

        if (is_array($competitionData) && isset($competitionData['autoCreateActuality'])) {
            $actuality = $this->competitionManager->createActuality($entityInstance);

            parent::persistEntity($entityManager, $actuality);

            $actualityAdminUrl = $this->adminUrlGenerator
                ->setController(PageCrudController::class)
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
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $resultsNotPersisted = $entityInstance->getResults()->filter(static fn (ResultCompetition $resultCompetition) => !$resultCompetition->getId());

        foreach ($resultsNotPersisted as $result) {
            $this->resultCompetitionManager->awardingBadges($result);
            $this->resultCompetitionManager->awardingRecord($result);
        }

        parent::updateEntity($entityManager, $entityInstance);

        $competitionUrl = $this->urlGenerator->generate(CompetitionController::ROUTE_LANDING_RESULTS_COMPETITION, [
            'slug' => $entityInstance->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->addFlash('success', 'Compétition mise à jour, lien public <a href="'.$competitionUrl.'">ici</a>');
    }
}
