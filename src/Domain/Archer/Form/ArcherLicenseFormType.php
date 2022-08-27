<?php

declare(strict_types=1);

namespace App\Domain\Archer\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ArcherLicenseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accidentInsurance', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
