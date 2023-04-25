<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;

class NewsletterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('types', EnumType::class, [
                'label' => 'Quel évènement voulez vous être notifier ?',
                'class' => NewsletterType::class,
                'multiple' => true,
                'expanded' => true,
                'choice_translation_domain' => 'newsletter',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'justine.bridges@example.com',
                ],
            ])
            ->add('licenseNumber', TextType::class, [
                'required' => true,
                'label' => 'Numéro de licence',
                'attr' => [
                    'pattern' => '[0-9]{6}[A-Za-z]',
                    'placeholder' => '123456A',
                ],
                'constraints' => [
                    new Regex('/[0-9]{6}[A-Za-z]/'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }
}
