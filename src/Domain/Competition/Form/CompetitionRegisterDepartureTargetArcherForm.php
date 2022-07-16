<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Type\GalleryType;
use App\Domain\File\Model\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CompetitionRegisterDepartureTargetArcherForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('civility', TextType::class)
            ->add('phone', TelType::class)
            ->add('license', TextType::class)
            ->add('category', TextType::class)
            ->add('club', TextType::class)
        ;
    }
}
