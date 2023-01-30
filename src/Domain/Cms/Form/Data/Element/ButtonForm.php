<?php

declare(strict_types=1);

namespace App\Domain\Cms\Form\Data\Element;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

final class ButtonForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextType::class, [
                'label' => 'Texte',
            ])
            ->add('url', UrlType::class, [
                'label' => 'Lien',
            ])
        ;
    }
}
