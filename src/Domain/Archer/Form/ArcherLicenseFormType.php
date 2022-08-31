<?php

declare(strict_types=1);

namespace App\Domain\Archer\Form;

use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\ArcherLicense;
use App\Domain\Archer\Model\License;
use App\Domain\File\Form\DocumentFormType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Valid;

class ArcherLicenseFormType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('license', EntityType::class, [
                'class' => License::class,
                'label' => 'Licence',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('license')
                        ->orderBy("CASE license.type WHEN 'adult' THEN 1 WHEN 'young' THEN 2 WHEN 'parasports' THEN 3 WHEN 'other' THEN 4 ELSE 5 END", 'ASC');
                },
                'group_by' => static fn (License $choice) => $choice->getType()?->toString(),
            ])
            ->add('individualInsurance', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'Je souscris à l’assurance individuelle accident avec ma licence (0.25€)' => true,
                    'Je refuse de souscrire à l’individuelle accident de la FFTA et dans ce cas je renonce à toute
                    indemnisation par l’assureur de la fédération en cas d’accident dans la pratique du tir à l’arc.' => false,
                ],
                'expanded' => true,
                'help' => 'L’assurance en responsabilité civile est incluse dans la licence.',
            ])
            ->add('fftaAttachedNoticeRead', CheckboxType::class, [
                'label' => 'Je reconnais avoir reçu la notice jointe, et avoir pris connaissance des garanties complémentaires
                            proposées par la FFTA.',
            ])
            ->add('needMainMedicalCertificate', CheckboxType::class, [
                'label' => 'Je présente un certificat médical datant de moins d’un an.',
                'required' => false,
                'mapped' => false,
            ])
            ->add('mainMedicalCertificate', DocumentFormType::class, [
                'label' => 'Certificat Médical',
            ])
            ->add('mainMedicalCertificateType', ChoiceType::class, [
                'label' => 'Type de certificat',
                'choices' => [
                    'Compétition' => '',
                    'Pratique' => '',
                ],
                'expanded' => true,
            ])
            ->add('runArchery', CheckboxType::class, [
                'label' => 'Je pratique le Run-Archery',
                'required' => false,
            ])
            ->add('runArcheryMedicalCertificate', DocumentFormType::class, [
                'label' => 'Certificat Médical pour le Run-archery',
            ])
            ->add('runArcheryMedicalCertificateType', ChoiceType::class, [
                'label' => 'Type de certificat de compétition',
                'choices' => [
                    'Course à pied' => '',
                    'Run-Archery' => '',
                ],
                'expanded' => true,
            ])
            ->add('weapons', ChoiceType::class, [
                'label' => 'Types d’arcs',
                'choices' => Weapon::toChoices(),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('photoUse', CheckboxType::class, [
                'label' => 'J’autorise l’utilisation de mon image (photos, vidéos,...) par mon club dans le cadre de
                            ses activités statutaires liées à ma pratique du tir à l’arc.',
            ])
            ->add('paymentChoice', ChoiceType::class, [
                'label' => 'Mode de règlement',
                'expanded' => true,
                'choices' => [
                    'Chèque' => '',
                    'Espèces' => '',
                    'Carte bancaire' => '',
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

        /** @var ?Archer $archer */
        $archer = $this->security->getUser();

        if ($archer && $archer->getBirthdayDate() && $archer->getBirthdayDate()->diff(new \DateTime())->y < 18) {
            // Pour les mineurs

            $builder
                ->add('contacts', CollectionType::class, [
                    'label' => 'Pour les mineurs, nom, prénom, téléphone des personnes à joindre en cas de besoin.',
                    'entry_type' => UnderageContactFormType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    'prototype_name' => '__underage_contact__',
                    'constraints' => [
                        new Valid(),
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArcherLicense::class,
        ]);
    }
}
