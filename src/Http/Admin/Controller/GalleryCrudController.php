<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Cms\Admin\Field\GalleryField;
use App\Domain\Cms\Model\Gallery;
use App\Domain\File\Admin\Field\PhotoField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GalleryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Gallery::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('form/gallery.html.twig')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $title = TextField::new('title')
            ->setLabel('Titre');
        $mainPhoto = PhotoField::new('mainPhoto')
            ->setLabel('Image principale');
        $gallery = GalleryField::new('photos');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $mainPhoto];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $mainPhoto, $gallery];
        } else {
            return [$title, $mainPhoto, $gallery];
        }
    }
}
