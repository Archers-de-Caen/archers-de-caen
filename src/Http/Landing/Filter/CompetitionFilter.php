<?php

declare(strict_types=1);

namespace App\Http\Landing\Filter;

use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Http\Landing\Request\CompetitionFilterDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Clock\now;

final class CompetitionFilter extends AbstractType
{
    public function __construct(
        private readonly CompetitionRepository $competitionRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $years = range(2000, ((int) now()->format('Y')) + 1);
        $years = array_reverse($years);

        $builder
            ->add('season', ChoiceType::class, [
                'label' => 'Saison',
                'choices' => array_combine($years, $years),
                'required' => false,
                'attr' => [
                    'value' => now()->format('Y'),
                ],
            ])

            ->add('location', ChoiceType::class, [
                'choices' => $this->competitionRepository->getAllLocations(),
                'choice_label' => static fn (?string $choice): ?string => $choice,
                'required' => false,
            ])

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
                'label' => 'Type',
                'choice_translation_domain' => 'competition',
                'required' => false,
                'class' => Type::class,
            ])
            ->get('type')
            ->addModelTransformer(new CallbackTransformer(
                function (?string $string): ?Type {
                    if (!$string) {
                        return null;
                    }

                    try {
                        return Type::from($string);
                    } catch (\ValueError) {
                        return null;
                    }
                },
                function (?Type $enum): ?string {
                    return $enum?->value;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionFilterDto::class,
            'csrf_protection' => false,
        ]);
    }
}
