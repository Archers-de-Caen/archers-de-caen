<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
final class TakeLicenseForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('license_form', UrlType::class, [
                'label' => 'Formulaire de demande de création de licence',
            ])
            ->add('assurance', UrlType::class, [
                'label' => 'Notice d\'assurance',
            ])
            ->add('health', UrlType::class, [
                'label' => 'Questionnaire santé',
            ])
        ;
    }
}
