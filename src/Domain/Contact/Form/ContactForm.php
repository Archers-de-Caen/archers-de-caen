<?php

declare(strict_types=1);

namespace App\Domain\Contact\Form;

use App\Domain\Contact\Config\Subject;
use App\Infrastructure\Google\Recaptcha;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContactForm extends AbstractType
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly Recaptcha $recaptcha,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?string $clientIp */
        $clientIp = $options['clientIp'];

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
            ])
            ->add('subject', EnumType::class, [
                'class' => Subject::class,
                'label' => 'Sujet',
                'expanded' => true,
                'choice_label' => static fn (Subject $subject) => $subject->value,
                'choice_translation_domain' => 'mail',
            ])
            ->add('recaptcha', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary g-recaptcha',
                    'data-sitekey' => $this->parameterBag->get('recaptcha_public'),
                    'data-callback' => 'onSubmit',
                    'data-action' => 'submit',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($clientIp): void {
            /** @var array $data */
            $data = $event->getData();

            /** @var string $recaptcha */
            $recaptcha = $data['recaptcha'];

            if (!$this->recaptcha->checkRecaptcha($recaptcha, $clientIp)) {
                $event->getForm()->addError(new FormError('Vous êtes un robot !'));
            }
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'clientIp' => null,
        ]);
    }
}
