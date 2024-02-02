<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Cms;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Model\Tag;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des tags des pages du site')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un tag de page au site')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Tag $tag): string => sprintf('Edition du tag <b>%s</b>', $tag))

            ->addFormTheme('form/ckeditor.html.twig')
            ->setDefaultSort(['createdAt' => 'DESC'])

            ->setEntityPermission(Archer::ROLE_DEVELOPER)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $name = TextField::new('name')
            ->setLabel('Tag');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $createdAt];
        }

        return [$name];
    }
}
