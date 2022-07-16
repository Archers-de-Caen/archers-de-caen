<?php

declare(strict_types=1);

namespace App\Domain\Result\Form;

use App\Domain\Archer\Model\Archer;
use App\Domain\Result\Model\ResultCompetition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultCompetitionForm extends ResultForm
{
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
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResultCompetition::class,
        ]);
    }
}
