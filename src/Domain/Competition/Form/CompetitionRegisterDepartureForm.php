<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Type\GalleryType;
use App\Domain\File\Model\Photo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CompetitionRegisterDepartureForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateTimeType::class, [
                'required' => true,
            ])
            ->add('targets', CollectionType::class, [
                'entry_type' => CompetitionRegisterDepartureTargetForm::class,
                'required' => true,
            ])
        ;
    }
}
