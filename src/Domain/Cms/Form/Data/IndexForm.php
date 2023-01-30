<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data;

use App\Domain\Cms\Form\Data\Element\ButtonForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
final class IndexForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('image', UrlType::class, [
                'label' => 'Image',
            ])
            ->add('button', ButtonForm::class)
        ;
    }
}
