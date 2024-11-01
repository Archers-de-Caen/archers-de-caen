<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data\Planning;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
class SlotForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start', NumberType::class, [
                'label' => 'Début',
                'attr' => [
                    'placeholder' => 'Doit être un chiffre entier',
                ],
            ])
            ->add('end', NumberType::class, [
                'label' => 'Fin',
                'attr' => [
                    'placeholder' => 'Doit être un chiffre entier',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => '17h - 18h',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Entrainement libre',
                ],
            ])
            ->add('color', ChoiceType::class, [
                'label' => 'Couleur',
                'choices' => [
                    'Primaire' => 'bg-primary',
                    'Secondaire' => 'bg-secondary',
                    'Rouge' => 'bg-red',
                    'Bleu' => 'bg-blue',
                    'Jaune' => 'bg-yellow text-grey',
                    'Gris' => 'bg-grey',
                    'Noir' => 'bg-black',
                    'Blanc' => 'bg-white',
                ],
            ])
        ;
    }
}
