<?php

declare(strict_types=1);

namespace App\Http\Landing\Filter;

use App\Domain\Archer\Config\Weapon;
use App\Domain\Badge\Model\Badge;
use App\Domain\Badge\Repository\BadgeRepository;
use App\Http\Landing\Request\BadgeFilterDto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BadgeFilter extends AbstractType
{
    use OnlyArcherLicencedTrait;

    public function __construct(private readonly BadgeRepository $badgeRepository)
    {
    }

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
            ->add('weapon', EnumType::class, [
                'label' => 'Arme',
                'choice_translation_domain' => 'archer',
                'required' => false,
                'class' => Weapon::class,
            ])
            ->get('weapon')
            ->addModelTransformer(new CallbackTransformer(
                static function (?string $string) : ?Weapon {
                    if (!$string) {
                        return null;
                    }
                    try {
                        return Weapon::from($string);
                    } catch (\ValueError) {
                        return null;
                    }
                },
                static function (?Weapon $enum) : ?string {
                    return $enum?->value;
                }
            ))
        ;

        $builder
            ->add('badge', EntityType::class, [
                'label' => 'Badge',
                'required' => false,
                'class' => Badge::class,
                'choice_translation_domain' => 'competition',
                'group_by' => static fn(Badge $badge) => $badge->getCompetitionType()?->name,
                'query_builder' => static fn(BadgeRepository $repository) => $repository->createQueryBuilder('b')
                    ->andWhere('b.type = :type')
                    ->setParameter('type', Badge::COMPETITION)
                    ->orderBy('b.name', 'ASC'),
            ])
            ->get('badge')
            ->addModelTransformer(new CallbackTransformer(
                function (?string $string): ?Badge {
                    return $string ? $this->badgeRepository->find($string) : null;
                },
                static function (?Badge $badge) : ?string {
                    return $badge ? (string) $badge->getId() : null;
                }
            ))
        ;

        $this->addOnlyArcherLicenced($builder);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadgeFilterDto::class,
            'csrf_protection' => false,
        ]);
    }
}
