<?php

declare(strict_types=1);

namespace App\Domain\Competition\Form;

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
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

use function Symfony\Component\Translation\t;

final class CompetitionRegisterDepartureTargetArcherForm extends AbstractType
{
    public function __construct(
        readonly private ArcherManager $archerManager,
        readonly private EntityManagerInterface $em
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CompetitionRegister $competitionRegister */
        $competitionRegister = $options['competitionRegister'];

        $builder
            ->add('firstName', TextType::class, [
                'translation_domain' => 'archer',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastName', TextType::class, [
                'translation_domain' => 'archer',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('gender', EnumType::class, [
                'label' => 'Genre',
                'class' => Gender::class,
                'choice_attr' => fn (Gender $gender) => ['data-gender' => $gender->value],
                'choice_label' => static fn (Gender $gender) => t($gender->value, domain: 'archer'),
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
                'translation_domain' => 'archer',
                'attr' => [
                    'pattern' => '[0-9]{10}',
                    'placeholder' => '0606060606',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('email', EmailType::class, [
                'translation_domain' => 'archer',
                'attr' => [
                    'placeholder' => 'contact@archers-caen.fr',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])
            ->add('licenseNumber', TextType::class, [
                'translation_domain' => 'archer',
                'attr' => [
                    'pattern' => '[0-9]{6}[A-Za-z]',
                    'placeholder' => '123456A',
                ],
                'constraints' => [
                    new Regex('/[0-9]{6}[A-Za-z]/'),
                ],
            ])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => Category::class,
                'choice_attr' => fn (Category $category) => ['data-gender' => $category->getGender(), 'data-category' => $category->value],
                'choice_label' => static fn (Category $category) => t($category->value, domain: 'archer'),
                'choices' => array_filter(Category::cases(), static fn (Category $category) => !$category->isOld()),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('club', TextType::class, [
                'translation_domain' => 'archer',
                'attr' => [
                    'placeholder' => 'Archers de Caen',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ])

            ->add('wheelchair', CheckboxType::class, [
                'translation_domain' => 'archer',
                'required' => false,
            ])
            ->add('weapon', EnumType::class, [
                'translation_domain' => 'archer',
                'class' => Weapon::class,
                'choice_label' => static fn (Weapon $weapon) => t($weapon->value, domain: 'archer'),
                'expanded' => true,
                'required' => true,
            ])
            ->add('firstYear', CheckboxType::class, [
                'translation_domain' => 'competition_register',
                'label_html' => true,
                'required' => false,
            ])
        ;

        foreach ($competitionRegister->getDepartures() as $departure) {
            $builder
                ->add($departure->getId().'-targets', EntityType::class, [
                    'class' => CompetitionRegisterDepartureTarget::class,
                    'translation_domain' => 'competition_register',
                    'label' => 'departures',
                    'expanded' => true,
                    'mapped' => false,
                    'choice_attr' => function () use ($departure) {
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

            $builder->addEventListener(FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($competitionRegister): void {
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
                                $error = new FormError(
                                    'Vous êtes déjà inscrit sur le départ du '.$departure,
                                );

                                $targetForm->addError($error);
                            }
                        }
                    }

                    if (!$targetCount) {
                        $event->getForm()->addError(new FormError('Vous devez sélectionner au moins, un départ !'));
                    }
                }
            );
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            /** @var array $registerArcher */
            $registerArcher = $event->getData();

            $emailIsSet = !empty($registerArcher['email']) && !str_contains($registerArcher['email'], '***');
            $phoneIsSet = !empty($registerArcher['phone']) && !str_contains($registerArcher['phone'], '***');

            if (
                $registerArcher['licenseNumber'] && !$emailIsSet && !$phoneIsSet &&
                $archer = $this->archerManager->findArcherFromLicense($registerArcher['licenseNumber'])
            ) {
                $registerArcher['email'] = $archer->getEmail();
                $registerArcher['phone'] = $archer->getPhone();

                $event->setData($registerArcher);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionRegisterDepartureTargetArcher::class,
            'competitionRegister' => null,
            'allow_extra_fields' => true,
        ]);
    }
}
