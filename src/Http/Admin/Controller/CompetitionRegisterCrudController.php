<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Form\CompetitionRegisterDepartureForm;
use App\Domain\Competition\Model\CompetitionRegister;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class CompetitionRegisterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompetitionRegister::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', "Formulaire d'inscription au concours de Caen")
            ->setPageTitle('new', "Ajouter un formulaire d'inscription")
            ->setPageTitle('detail', fn (CompetitionRegister $competitionRegister) => (string) $competitionRegister)
            ->setPageTitle('edit', fn (CompetitionRegister $competitionRegister) => sprintf("Edition du formulaire l'inscription <b>%s</b>", $competitionRegister))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, 'new', fn (Action $action) => $action->setLabel("Créer un formulaire d'inscription"))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $type = ChoiceField::new('types', 'Types de concours')
            ->setChoices(Type::toChoicesWithEnumValue())
            ->allowMultipleChoices();
        $dateStart = DateField::new('dateStart', 'Date de début');
        $dateEnd = DateField::new('dateEnd', 'Date de fin');
        $departures = CollectionField::new('departures', 'Départs')
                ->setEntryType(CompetitionRegisterDepartureForm::class);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $dateStart, $dateEnd];
        }

        return [$type, $dateStart, $dateEnd, $departures];
    }
}
