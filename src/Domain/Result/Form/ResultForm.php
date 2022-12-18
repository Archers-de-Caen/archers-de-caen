<?php

declare(strict_types=1);

namespace App\Domain\Result\Form;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Result\Model\Result;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ResultForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EnumType::class, [
                'translation_domain' => 'archer',
                'class' => Category::class,
                'label' => 'Catégorie',
                'choice_label' => static fn (Category $category) => $category->value,
            ])
            ->add('rank', IntegerType::class, [
                'label' => 'Classement',
            ])
            ->add('score', IntegerType::class, [
                'required' => true,
                'label' => 'Score',
            ])
            ->add('weapon', EnumType::class, [
                'translation_domain' => 'archer',
                'class' => Weapon::class,
                'label' => 'Arme',
                'choice_label' => static fn (Weapon $weapon) => $weapon->value,
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
            'data_class' => Result::class,
        ]);
    }
}
