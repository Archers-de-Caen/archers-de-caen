<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use App\Domain\File\Model\Document;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

class CompetitionRegisterForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateStart', DateTimeType::class, [
                'required' => true,
            ])
            ->add('dateEnd', DateTimeType::class, [
                'required' => true,
            ])
            ->add('departure', CollectionType::class, [
                'entry_type' => CompetitionRegisterDepartureForm::class,
                'required' => true,
            ])
            ->add('mandate', EntityType::class, [
                'class' => Document::class,
            ])
        ;
    }
}
