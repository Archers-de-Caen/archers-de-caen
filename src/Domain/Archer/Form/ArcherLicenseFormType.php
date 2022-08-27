<?php

declare(strict_types=1);

namespace App\Domain\Archer\Form;

use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\License;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ArcherLicenseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('membershipNumber', TextType::class)
            ->add('lastname', TextType::class)
            ->add('firstname', TextType::class)
            ->add('birthdayDate', DateType::class)
            ->add('gender', ChoiceType::class, [
                'choices' => Gender::toChoices(),
            ])
            ->add('postalAddress', TextType::class)
            ->add('email', EmailType::class)
            ->add('phone', TextType::class)
            ->add('nationality', TextType::class, [
                'empty_data' => 'Française',
            ])
            ->add('license', EntityType::class, [
                'class' => License::class,
            ])
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
