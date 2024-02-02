<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data\ManagementTeam;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
final class ManagementTeamForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Commission',
            ])
            ->add('order', NumberType::class, [
                'label' => 'Ordre',
                'scale' => 0,
            ])
            ->add('archers', CollectionType::class, [
                'entry_type' => ManagementTeamArcherForm::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__management_team_archer__',
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;
    }
}
