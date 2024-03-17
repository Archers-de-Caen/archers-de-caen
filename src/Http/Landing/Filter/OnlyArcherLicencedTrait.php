<?php

declare(strict_types=1);

namespace App\Http\Landing\Filter;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

trait OnlyArcherLicencedTrait
{
    private function addOnlyArcherLicenced(FormBuilderInterface $builder): void
    {
        $builder
            ->add('onlyArcherLicenced', CheckboxType::class, [
                'label' => 'Seulement les archers licenciés',
                'required' => false,
            ])
        ;
    }
}
