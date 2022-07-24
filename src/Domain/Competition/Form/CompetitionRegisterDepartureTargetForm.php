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
                'label' => 'Diamètre / Type',
                'required' => true,
                'choices' => [
                    'Mono-spots' => [
                        'ø 40' => 'm40',
                        'ø 60' => 'm60',
                        'ø 80' => 'm80',
                        'ø 80 réduit' => 'r80',
                        'ø 122' => 'm122',
                    ],
                    'Tri-spots' => [
                        'ø 40' => 't40',
                        'ø 60' => 't60',
                    ],
                ],
            ])
            ->add('distance', ChoiceType::class, [
                'required' => true,
                'label' => 'Distance',
                'choices' => [
                    '10 m' => 10,
                    '15 m' => 15,
                    '18 m' => 18,
                    '20 m' => 20,
                    '25 m' => 25,
                    '30 m' => 30,
                    '40 m' => 40,
                    '50 m' => 50,
                    '60 m' => 60,
                    '70 m' => 70,
                ],
            ])
        ;
    }
}
