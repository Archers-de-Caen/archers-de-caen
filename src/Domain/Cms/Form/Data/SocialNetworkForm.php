<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Appelé depuis App\Http\Admin\Controller\DataCrudController::createEditFormBuilder.
 */
final class SocialNetworkForm extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('facebook', UrlType::class, [
                'label' => 'Facebook',
            ])
            ->add('instagram', UrlType::class, [
                'label' => 'Instagram',
            ])
            ->add('youtube', UrlType::class, [
                'label' => 'Youtube',
            ])
            ->add('tiktok', UrlType::class, [
                'label' => 'tiktok',
            ])
        ;
    }
}
