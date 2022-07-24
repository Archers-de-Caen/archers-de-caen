<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Type\GalleryType;
use App\Domain\File\Model\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CompetitionRegisterDepartureForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'required' => true,
                'label' => 'Date',
                'widget' => 'single_text',
            ])
            ->add('maxRegistrations', NumberType::class, [
                'required' => true,
                'label' => 'Quota',
                'html5' => true,
            ])
            ->add('targets', CollectionType::class, [
                'entry_type' => CompetitionRegisterDepartureTargetForm::class,
                'label' => 'Cibles',
                'required' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__competition_register_departure_target__',
                'constraints' => [
                    new Valid(),
                ],
            ])
        ;

        $builder->get('date')->addModelTransformer(new CallbackTransformer(
            static function (?array $arrayToDateTime): ?\DateTime {
                if (isset($arrayToDateTime['date'])) {
                    return new \DateTime($arrayToDateTime['date'], new \DateTimeZone($arrayToDateTime['timezone']));
                }

                return null;
            },
            static function ($dateTimeToArray) {
                return $dateTimeToArray;
            }
        ));
    }
}
