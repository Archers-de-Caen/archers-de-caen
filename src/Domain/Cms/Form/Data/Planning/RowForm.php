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
class RowForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('day', TextType::class, [
                'label' => 'Jour',
            ])
            ->add('slots', CollectionType::class, [
                'entry_type' => SlotForm::class,
                'label' => 'Créneaux',
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }
}
