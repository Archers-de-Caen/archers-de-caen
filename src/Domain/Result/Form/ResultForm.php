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

use function Symfony\Component\Translation\t;

abstract class ResultForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EnumType::class, [
                'class' => Category::class,
                'label' => 'Catégorie',
                'choice_label' => static fn (Category $category): \Symfony\Component\Translation\TranslatableMessage => t($category->value, domain: 'archer'),
            ])
            ->add('rank', IntegerType::class, [
                'label' => 'Classement',
            ])
            ->add('score', IntegerType::class, [
                'required' => true,
                'label' => 'Score',
            ])
            ->add('weapon', EnumType::class, [
                'class' => Weapon::class,
                'label' => 'Arme',
                'choice_label' => static fn (Weapon $weapon): \Symfony\Component\Translation\TranslatableMessage => t($weapon->value, domain: 'archer'),
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
