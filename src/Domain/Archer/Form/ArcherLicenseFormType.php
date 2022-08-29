<?php

declare(strict_types=1);

namespace App\Domain\Archer\Form;

use App\Domain\Archer\Model\ArcherLicense;
use App\Domain\Archer\Model\License;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArcherLicenseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('license', EntityType::class, [
                'class' => License::class,
                'label' => 'Licence',
            ])
            ->add('accidentInsurance', ChoiceType::class, [
                'choices' => [
                    'Je souscris à l’assurance individuelle accident avec ma licence (0.25€)' => true,
                    'Je refuse de souscrire à l’individuelle accident de la FFTA et dans ce cas je renonce à toute
                    indemnisation par l’assureur de la fédération en cas d’accident dans la pratique du tir à l’arc.' => false,
                ]
            ])
            ->add('contacts', TextType::class, [
                'label' => 'Pour les mineurs, nom, prénom, téléphone des personnes à joindre en cas de besoin.',
            ])
            ->add('fftaAttachedNoticeRead', CheckboxType::class, [
                'label' => 'Je reconnais avoir reçu la notice jointe, et avoir pris connaissance des garanties complémentaires
                            proposées par la FFTA.',
            ])
            ->add('mainMedicalCertificate', TextType::class, [
                'label' => '',
            ])
            ->add('mainMedicalCertificateType', ChoiceType::class, [
                'label' => 'Type de certificat',
                'choices' => [
                    'Compétition' => '',
                    'Pratique' => '',
                ]
            ])
            ->add('runArchery', CheckboxType::class, [
                'label' => 'Je pratique le Run-Archery',
            ])
            ->add('runArcheryMedicalCertificate', TextType::class, [
                'label' => '',
            ])
            ->add('runArcheryMedicalCertificateType', ChoiceType::class, [
                'label' => 'Type de certificat de compétition',
                'choices' => [
                    'Course à pied' => '',
                    'Run-Archery' => '',
                ]
            ])
            ->add('weapons', TextType::class, [
                'label' => 'Type d’arc',
            ])
            ->add('photoUse', CheckboxType::class, [
                'label' => 'J’autorise l’utilisation de mon image (photos, vidéos,...) par mon club dans le cadre de
                            ses activités statutaires liées à ma pratique du tir à l’arc.',
            ])
            ->add('paymentChoice', ChoiceType::class, [
                'label' => 'Mode de règlement',
                'choices' => [
                    'Chèque' => '',
                    'Espèces' => '',
                    'Carte bancaire (via le site)' => '',
                    'Pass\'port' => '',
                    'Virement' => '',
                    'Atouts Normandie' => '',
                ],
            ])
            ->add('fftaNewsletter', CheckboxType::class, [
                'label' => 'J’accepte de recevoir la newsletter de la FFTA (1 à 2 par mois).',
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn -primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArcherLicense::class,
        ]);
    }
}
