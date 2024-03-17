<?php

declare(strict_types=1);

namespace App\Http\Landing\Filter;

use App\Http\Landing\Request\ArrowFilterDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ArrowFilter extends AbstractType
{
    use OnlyArcherLicencedTrait;

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reset', SubmitType::class, [
                'label' => 'Réinitialiser',
                'attr' => [
                    'class' => 'btn',
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn -primary',
                ],
            ])
        ;

        $this->addOnlyArcherLicenced($builder);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArrowFilterDto::class,
            'csrf_protection' => false,
        ]);
    }
}
