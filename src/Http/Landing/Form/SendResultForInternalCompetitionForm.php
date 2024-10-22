<?php

declare(strict_types=1);

namespace App\Http\Landing\Form;

use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SendResultForInternalCompetitionForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('archer', EntityType::class, [
                'class' => Archer::class,
                'query_builder' => static fn (ArcherRepository $queryBuilder): QueryBuilder => $queryBuilder
                    ->createQueryBuilder('archer')
                    ->leftJoin('archer.archerLicenses', 'al', 'WITH', 'al.active = TRUE')
                    ->andWhere('al.id IS NOT NULL')
                    ->orderBy('archer.firstName', 'ASC'),
                'label' => 'Archer',
                'required' => true,
                'autocomplete' => true,
            ])
            ->add('weapon', EnumType::class, [
                'class' => Weapon::class,
                'label' => 'Arme',
                'translation_domain' => 'archer',
                'required' => true,
            ])
            ->add('category', EnumType::class, [
                'class' => Gender::class,
                'choices' => [
                    Gender::WOMAN,
                    Gender::MAN,
                ],
                'label' => 'Catégorie',
                'translation_domain' => 'archer',
                'required' => true,
            ])
            ->add('score', IntegerType::class, [
                'label' => 'Score',
                'required' => true,
            ])
            ->add('completionDate', DateType::class, [
                'label' => 'Date de réalisation',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
