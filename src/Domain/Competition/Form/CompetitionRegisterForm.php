<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\File\Model\Document;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

final class CompetitionRegisterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('types', EnumType::class, [
                'label' => 'Types de concours',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'class' => Type::class,
            ])
            ->add('dateStart', DateType::class, [
                'label' => 'Date de début',
                'required' => true,
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'Date de fin',
                'required' => true,
            ])
            ->add('departures', CollectionType::class, [
                'label' => 'Départs',
                'entry_type' => CompetitionRegisterDepartureForm::class,
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__competition_register_departure__',
                'constraints' => [
                    new Valid(),
                ],
            ])
            ->add('mandate', EntityType::class, [
                'class' => Document::class,
                'label' => 'Mandat',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionRegister::class,
        ]);
    }
}
