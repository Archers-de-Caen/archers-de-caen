<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data;

use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
final class FaqForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Question',
            ])
            ->add('answer', TextEditorType::class, [
                'label' => 'Réponse',
                'attr' => [
                    'class' => 'field-text_editor',
                ],
            ])
        ;
    }
}
