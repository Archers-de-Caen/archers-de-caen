<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Http\Landing\Controller\DefaultController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArcherCrudController extends AbstractCrudController
{
    public function __construct(readonly private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Archer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des archers')
            ->setPageTitle('new', 'Ajouter un archer')
            ->setPageTitle('detail', fn (Archer $archer) => (string) $archer)
            ->setPageTitle('edit', fn (Archer $archer) => sprintf('Edition de l\'archer <b>%s</b>', $archer))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
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
            ->setChoices(Gender::cases())
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', Gender::class)
            ->setLabel('Genre')
        ;

        $category = ChoiceField::new('category')
            ->setChoices(Category::cases())
            ->setFormType(EnumType::class)
            ->setFormTypeOption('class', Category::class)
            ->setLabel('Catégorie')
        ;

        /**
         * Todo: https://github.com/EasyCorp/EasyAdminBundle/pull/4988
         */
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL], true)) {
            $category->setChoices(array_reduce(
                Category::cases(),
                static fn (array $choices, Category $category) => $choices + [$category->name => $category->value],
                [],
            ));

            $gender->setChoices(array_reduce(
                Gender::cases(),
                static fn (array $choices, Gender $gender) => $choices + [$gender->name => $gender->value],
                [],
            ));
        }

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            if ($this->isGranted(Archer::ROLE_DEVELOPER)) {
                yield $id;
            }

            yield $createdAt;
        }

        yield $licenseNumber;
        yield $firstName;
        yield $lastName;
        yield $email;
        yield $phone;
        yield $gender;
        yield $category;
    }

    public function configureActions(Actions $actions): Actions
    {
        $impersonation = Action::new('Se connecter')->linkToUrl(
            function (Archer $archer): string {
                return $this->urlGenerator->generate(
                    DefaultController::ROUTE_LANDING_INDEX,
                    ['_switch_user' => $archer->getEmail()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
        );

        return $actions
            ->add(Crud::PAGE_INDEX, $impersonation)
        ;
    }
}
