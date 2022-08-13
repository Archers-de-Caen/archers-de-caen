<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Competition\Config\TargetType;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTarget;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionRegisterDepartureTargetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Diamètre / Type',
                'required' => true,
                'choices' => TargetType::toChoices(),
            ])
            ->add('distance', ChoiceType::class, [
                'required' => true,
                'label' => 'Distance',
                'choices' => [
                    '10 m' => 10,
                    '15 m' => 15,
                    '18 m' => 18,
                    '20 m' => 20,
                    '25 m' => 25,
                    '30 m' => 30,
                    '40 m' => 40,
                    '50 m' => 50,
                    '60 m' => 60,
                    '70 m' => 70,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionRegisterDepartureTarget::class,
        ]);
    }
}
