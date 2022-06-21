<?php

declare(strict_types=1);

namespace App\Domain\Result\Form;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Result\Model\ResultCompetition;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultCompetitionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('archer', EntityType::class, [
                'class' => Archer::class,
                'attr' => [
                    'data-ea-widget' => 'ea-autocomplete',
                    'data-ea-autocomplete-allow-item-create' => 'true',
                ],
                'required' => true,
            ])
            ->add('category', EnumType::class, [
                'class' => Category::class,
                'label' => 'Catégorie',
                'choice_label' => static fn (Category $category) => $category->toString(),
            ])
            ->add('rank', IntegerType::class, [
                'label' => 'Classement',
            ])
            ->add('score', IntegerType::class, [
                'required' => true,
            ])
            ->add('weapon', EnumType::class, [
                'class' => Weapon::class,
                'choice_label' => static fn (Weapon $weapon) => $weapon->toString(),
                'required' => true,
            ])
            ->add('completionDate', DateType::class, [
                'label' => 'Date de réalisation',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResultCompetition::class,
        ]);
    }
}
