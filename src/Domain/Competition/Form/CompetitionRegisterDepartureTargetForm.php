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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CompetitionRegisterDepartureTargetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [

                ],
            ])
            ->add('distance', NumberType::class, [
                'required' => true,
            ])
            ->add('maxRegistrations', NumberType::class, [
                'required' => true,
            ])
            ->add('registered', CollectionType::class, [
                'required' => true,
                'entry_type' => CompetitionRegisterDepartureTargetArcherForm::class,
            ])
        ;
    }
}
