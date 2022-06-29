<?php

declare(strict_types=1);

namespace App\Domain\Result\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultTeamDuelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('score', IntegerType::class, [
                'label' => 'Score',
            ])
            ->add('opponentName', TextType::class, [
                'label' => 'Nom de l\'adversaire',
            ])
            ->add('opponentScore', IntegerType::class, [
                'label' => 'Score de l\'adversaire',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}