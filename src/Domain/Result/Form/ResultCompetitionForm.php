<?php

declare(strict_types=1);

namespace App\Domain\Result\Form;

use App\Domain\Archer\Model\Archer;
use App\Domain\Result\Model\ResultCompetition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ResultCompetitionForm extends ResultForm
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('archer', EntityType::class, [
                'class' => Archer::class,
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'data-ea-autocomplete-allow-item-create' => 'true',
                ],
                'required' => true,
            ])
            ->add('record', CheckboxType::class, [
                'label' => 'Record perso ?',
                'required' => false,
            ]);

        parent::buildForm($builder, $options);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResultCompetition::class,
        ]);
    }
}
