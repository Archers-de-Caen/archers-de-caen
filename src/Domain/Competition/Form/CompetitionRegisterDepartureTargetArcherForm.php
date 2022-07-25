<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CompetitionRegisterDepartureTargetArcherForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => Gender::toChoicesBasic(),
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ]
            ])
            ->add('licenseNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'attr' => [
                    'pattern' => '[0-9]{6}[A-Za-z]',
                    'placeholder' => '123456A',
                ],
                'constraints' => [
                    new Regex('/[0-9]{6}[A-Za-z]/'),
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => Category::toChoices(),
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('club', TextType::class, [
                'label' => 'Club',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
            ])
        ;
    }
}
