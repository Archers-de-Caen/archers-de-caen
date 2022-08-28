<?php

declare(strict_types=1);

namespace App\Domain\Archer\Form;

use App\Domain\Archer\Model\PostalAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostalAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('line1', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Rue, boîte postale..',
                ],
            ])
            ->add('line2', TextType::class, [
                'label' => "Complément d'adresse (facultatif)",
                'required' => false,
                'attr' => [
                    'placeholder' => 'Appartement, suite, bâtiment, entrée, étage ...',
                ],
            ])
            ->add('line3', TextType::class, [
                'label' => "Complément d'adresse (facultatif)",
                'required' => false,
                'attr' => [
                    'placeholder' => 'Appartement, suite, bâtiment, entrée, étage ...',
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Ville',
                ],
            ])
            ->add('postcode', TextType::class, [
                'label' => 'Code Postal',
                'attr' => [
                    'placeholder' => 'Code Postal',
                ],
            ])
            ->add('country', TextType::class, [
                'label' => 'Pays',
                'empty_data' => 'FR',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostalAddress::class,
        ]);
    }
}

