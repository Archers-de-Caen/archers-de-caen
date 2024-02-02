<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Competition\Model\CompetitionRegisterDeparture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CompetitionRegisterDepartureForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'required' => true,
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('maxRegistration', NumberType::class, [
                'required' => true,
                'label' => 'Quota',
                'html5' => true,
            ])
            ->add('targets', CollectionType::class, [
                'entry_type' => CompetitionRegisterDepartureTargetForm::class,
                'label' => 'Cibles',
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__competition_register_departure_target__',
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionRegisterDeparture::class,
        ]);
    }
}
