<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

use Symfony\Component\Translation\TranslatableMessage;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Manager\ArcherManager;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTarget;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CompetitionRegisterDepartureTargetArcherForm extends AbstractType
{
    public function __construct(readonly private ArcherManager $archerManager, readonly private EntityManagerInterface $em)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Link',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('gender', EnumType::class, [
                'label' => 'Genre',
                'class' => Gender::class,
                'choice_attr' => fn (Gender $gender): array => ['data-gender' => $gender->value],
                'choice_label' => static fn (Gender $gender): TranslatableMessage => t($gender->value, domain: 'archer'),
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices' => [
                    Gender::MAN,
                    Gender::WOMAN,
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'pattern' => '[0-9]{10}',
                    'placeholder' => '0606060606',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'contact@archers-caen.fr',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])
            ->add('licenseNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'help' => 'Depuis la saison 2023-2024, le numéro de licence est composé de 7 chiffres et d\'une lettre',
                'attr' => [
                    'pattern' => '[0-9]{7}[A-Za-z]',
                    'placeholder' => '0123456A',
                ],
                'constraints' => [
                    new Regex('/[0-9]{7}[A-Za-z]/'),
                ],
            ])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_attr' => fn (Category $category): array => ['data-gender' => $category->getGender(), 'data-category' => $category->value],
                'choice_label' => static fn (Category $category): TranslatableMessage => t($category->value, domain: 'archer'),
                'choices' => array_filter(Category::cases(), static fn (Category $category): bool => !$category->isOld()),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('club', TextType::class, [
                'label' => 'Club',
                'attr' => [
                    'placeholder' => 'Archers de Caen',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])

            ->add('wheelchair', CheckboxType::class, [
                'label' => 'Tir en fauteuil roulant',
                'required' => false,
            ])
            ->add('weapon', EnumType::class, [
                'label' => 'Arme',
                'class' => Weapon::class,
                'choice_label' => static fn (Weapon $weapon): TranslatableMessage => t($weapon->value, domain: 'archer'),
                'expanded' => true,
                'required' => true,
            ])
            ->add('firstYear', CheckboxType::class, [
                'label' => '1<sup>er</sup> année de licence et souhaite effectuer le tir en débutant',
                'label_html' => true,
                'required' => false,
            ])
            ->add('additionalInformation', TextareaType::class, [
                'label' => 'Autre chose ?',
                'required' => false,
            ])
        ;

        /** @var ?CompetitionRegister $competitionRegister */
        $competitionRegister = $options['competitionRegister'];

        if ($competitionRegister) {
            foreach ($competitionRegister->getDepartures() as $departure) {
                $builder
                    ->add($departure->getId().'-targets', EntityType::class, [
                        'class' => CompetitionRegisterDepartureTarget::class,
                        'label' => 'Départs',
                        'expanded' => true,
                        'mapped' => false,
                        'choice_attr' => function () use ($departure): array {
                            return [
                                'disabled' => $departure->getRegistration() >= $departure->getMaxRegistration(),
                            ];
                        },
                        'query_builder' => function (EntityRepository $er) use ($departure) {
                            return $er->createQueryBuilder('crdt')
                                ->join('crdt.departure', 'departure')
                                ->where('departure.id = :uuid')
                                ->setParameter('uuid', $departure->getId(), 'uuid');
                        },
                    ]);

                $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($competitionRegister): void {
                    /** @var CompetitionRegisterDepartureTargetArcher $registerArcher */
                    $registerArcher = $event->getData();
                    $targetCount = 0;

                    foreach ($competitionRegister->getDepartures() as $departure) {
                        $targetForm = $event->getForm()->get($departure->getId().'-targets');

                        if ($targetForm->getData()) {
                            ++$targetCount;

                            /** @var int $count */
                            $count = $this->em->getRepository(CompetitionRegisterDepartureTargetArcher::class)
                                ->createQueryBuilder('archer')
                                ->select('count(archer.id)')
                                ->join('archer.target', 'target')
                                ->join('target.departure', 'departure')
                                ->where('archer.licenseNumber = :licenseNumber')
                                ->andWhere('departure.id = :departure')
                                ->setParameter('licenseNumber', $registerArcher->getLicenseNumber())
                                ->setParameter('departure', $departure->getId(), 'uuid')
                                ->getQuery()
                                ->getSingleScalarResult();

                            if ($count) {
                                $targetForm->addError(new FormError('Vous êtes déjà inscrit sur le départ du '.$departure));
                            }
                        }
                    }

                    if (!$targetCount) {
                        $event->getForm()->addError(new FormError('Vous devez sélectionner au moins, un départ !'));
                    }
                });
            }
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            /** @var array $registerArcher */
            $registerArcher = $event->getData();

            if (
                $registerArcher['licenseNumber']
                && empty($registerArcher['email'])
                && empty($registerArcher['phone'])
                && $archer = $this->archerManager->findArcherFromLicense($registerArcher['licenseNumber'])
            ) {
                $registerArcher['email'] = $archer->getEmail();
                $registerArcher['phone'] = $archer->getPhone();

                $event->setData($registerArcher);
            }
        });

        $builder->add('submit', SubmitType::class, [
            'label' => 'Valider',
            'attr' => [
                'class' => 'btn -primary',
            ],
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionRegisterDepartureTargetArcher::class,
            'competitionRegister' => null,
            'allow_extra_fields' => true,
        ]);
    }
}
