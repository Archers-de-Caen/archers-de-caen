<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data\Planning;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
class PlanningForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('headers', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'label' => 'L\'heure',
                        'placeholder' => '17h',
                    ],
                ],
                'label' => 'Entêtes',
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('rows', CollectionType::class, [
                'entry_type' => RowForm::class,
                'label' => 'Lignes',
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }
}
