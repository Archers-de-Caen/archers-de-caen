<?php

declare(strict_types=1);

namespace App\Http\Landing\Filter;

use App\Domain\Archer\Config\Weapon;
use App\Domain\Competition\Config\Type;
use App\Http\Landing\Request\RecordFilterDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecordFilter extends AbstractType
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

        $builder
            ->add('type', EnumType::class, [
                'label' => 'Type de concours',
                'choice_translation_domain' => 'competition',
                'required' => false,
                'class' => Type::class,
            ])
            ->get('type')
            ->addModelTransformer(new CallbackTransformer(
                static function (?string $string): ?Type {
                    if (!$string) {
                        return null;
                    }

                    try {
                        return Type::from($string);
                    } catch (\ValueError) {
                        return null;
                    }
                },
                static function (?Type $enum): ?string {
                    return $enum?->value;
                }
            ))
        ;

        $builder
            ->add('weapon', EnumType::class, [
                'label' => 'Arme',
                'choice_translation_domain' => 'archer',
                'required' => false,
                'class' => Weapon::class,
            ])
            ->get('weapon')
            ->addModelTransformer(new CallbackTransformer(
                static function (?string $string): ?Weapon {
                    if (!$string) {
                        return null;
                    }

                    try {
                        return Weapon::from($string);
                    } catch (\ValueError) {
                        return null;
                    }
                },
                static function (?Weapon $enum): ?string {
                    return $enum?->value;
                }
            ))
        ;

        $this->addOnlyArcherLicenced($builder);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecordFilterDto::class,
            'csrf_protection' => false,
        ]);
    }
}
