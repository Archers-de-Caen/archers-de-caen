<?php

declare(strict_types=1);

namespace App\Domain\Result\Form;

use App\Domain\Archer\Model\Archer;
use App\Domain\Result\Model\ResultTeam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ResultTeamForm extends ResultForm
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('category')
            ->add('teammates', EntityType::class, [
                'class' => Archer::class,
                'multiple' => true,
                'label' => 'Coéquipier',
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'data-ea-autocomplete-allow-item-create' => 'true',
                ],
                'required' => true,
            ])
            ->add('duels', CollectionType::class, [
                'label' => 'Duels',
                'entry_type' => ResultTeamDuelForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__duels__',
                'constraints' => [
                    new Valid(),
                ],
            ])
            ->add('finalRankings', CollectionType::class, [
                'label' => 'Classement final',
                'entry_type' => ResultTeamFinalRankForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__finalRankings__',
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResultTeam::class,
        ]);
    }
}
