<?php

declare(strict_types=1);

namespace App\Domain\Contact\Form;

use App\Domain\Contact\Config\Subject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Votre nom',
                'attr' => [
                    'placeholder' => 'Legolas Greenleaf',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => 'legolas-greenleaf@foret-noire.sda',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'placeholder' => 'Un soleil rouge se lève, beaucoup de sang a du couler cette nuit...',
                ],
            ])
            ->add('subject', EnumType::class, [
                'class' => Subject::class,
                'label' => 'Sujet',
                'expanded' => true,
                'choice_label' => static fn (Subject $subject) => $subject->toString(),
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }
}
