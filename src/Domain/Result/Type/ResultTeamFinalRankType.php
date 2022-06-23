<?php

declare(strict_types=1);

namespace App\Domain\Result\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultTeamFinalRankType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamName', TextType::class, [
                'label' => 'Nom de l\'équipe',
            ])
            ->add('rank', IntegerType::class, [
                'label' => 'Classement',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
