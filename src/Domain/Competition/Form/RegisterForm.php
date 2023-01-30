<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegisterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registrations', CollectionType::class, [
                'label' => 'Inscription',
                'entry_type' => CompetitionRegisterDepartureTargetArcherForm::class,
                'allow_add' => true,
                'entry_options' => [
                    'competitionRegister' => $options['competitionRegister'],
                ],
                'mapped' => false,
            ])
            ->add('additionalInformation', TextareaType::class, [
                'translation_domain' => 'competition_register',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn -primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'competitionRegister' => null,
        ]);
    }
}
