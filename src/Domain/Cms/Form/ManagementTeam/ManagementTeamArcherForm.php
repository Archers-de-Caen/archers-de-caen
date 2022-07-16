<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\ManagementTeam;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ManagementTeamArcherForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('function', TextType::class, [
                'label' => 'Fonction',
            ])
            ->add('image', UrlType::class, [
                'label' => 'Photo',
            ])
        ;
    }
}
