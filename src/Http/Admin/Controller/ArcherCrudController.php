<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Archer\Service\ArcherService;
use App\Domain\Newsletter\NewsletterType;
use App\Http\Landing\Controller\IndexController;
use App\Infrastructure\Service\ArcheryService;
use App\Infrastructure\Service\FFTA\FFTADirigeantService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class ArcherCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ArcherRepository $archerRepository,
        private readonly ArcherService $archerService,
        private readonly FFTADirigeantService $fftaDirigeantService,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Archer::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Liste des archers')
            ->setPageTitle('new', 'Ajouter un archer')
            ->setPageTitle('detail', static fn (Archer $archer): string => (string) $archer)
            ->setPageTitle('edit', static fn (Archer $archer): string => \sprintf("Edition de l'archer <b>%s</b>", $archer));
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $licenseNumber = TextField::new('licenseNumber')
            ->setLabel('Numéro de licence');

        $firstName = TextField::new('firstName')
            ->setLabel('Prénom');

        $lastName = TextField::new('lastName')
            ->setLabel('Nom');

        $phone = TextField::new('phone')
            ->setLabel('Téléphone');

        $email = EmailField::new('email');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $gender = ChoiceField::new('gender')
            ->setLabel('Genre')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Gender::class,
                'choice_label' => static fn (Gender $choice): TranslatableMessage => t($choice->value, domain: 'archer'),
                'choices' => Gender::cases(),
            ])
            ->formatValue(static fn ($value, ?Archer $entity): ?TranslatableMessage => $entity?->getGender()?->value ? t($entity->getGender()->value, domain: 'archer') : null);

        $category = ChoiceField::new('category')
            ->setLabel('Catégorie')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Category::class,
                'choice_label' => static fn (Category $choice): TranslatableMessage => t($choice->value, domain: 'archer'),
                'choices' => Category::cases(),
            ])
            ->formatValue(static fn ($value, ?Archer $entity): ?TranslatableMessage => $entity?->getCategory()?->value ? t($entity->getCategory()->value, domain: 'archer') : null);

        $newsletters = ChoiceField::new('newsletters')
            ->setLabel('Inscrit aux newsletters')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => NewsletterType::class,
                'choice_label' => static fn (NewsletterType $choice): TranslatableMessage => t(strtoupper($choice->value), domain: 'newsletter'),
                'choices' => NewsletterType::cases(),
                'multiple' => true,
            ])
            ->setTranslatableChoices(function (Archer $archer): array {
                return array_reduce(
                    $archer->getNewsletters(),
                    static fn (array $carry, NewsletterType $choice): array => $carry + [$choice->value => t($choice->name, domain: 'newsletter')],
                    NewsletterType::cases()
                );
            });

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            if ($this->isGranted(Archer::ROLE_DEVELOPER)) {
                yield $id;
            }

            yield $createdAt;
        }

        yield $licenseNumber;
        yield $firstName;
        yield $lastName;
        yield $email;
        yield $phone;
        yield $gender;
        yield $category;
        yield $newsletters;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $impersonation = Action::new('Se connecter')->linkToUrl(
            function (Archer $archer): string {
                return $this->urlGenerator->generate(
                    IndexController::ROUTE,
                    ['_switch_user' => $archer->getEmail()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
        );

        $mergeArchers = Action::new('mergeArchers')
            ->setLabel('Fusionner des archers')
            ->linkToCrudAction('mergeArchers')
            ->createAsGlobalAction();

        $downloadContactFromFfta = Action::new('downloadContactFromFfta')
            ->setLabel('Google contact')
            ->linkToCrudAction('downloadContactFromFfta')
            ->createAsGlobalAction();

        return $actions
            ->add(Crud::PAGE_INDEX, $impersonation)
            ->add(Crud::PAGE_INDEX, $downloadContactFromFfta)
            ->add(Crud::PAGE_INDEX, $mergeArchers);
    }

    /**
     * @internal
     */
    #[AdminRoute(
        path: '/merge',
        name: 'admin_archer_publish',
    )]
    public function mergeArchers(AdminContext $context): Response
    {
        if (Request::METHOD_POST === $context->getRequest()->getMethod()) {
            $base = $context->getRequest()->request->get('archer-base');
            $toMerge = $context->getRequest()->request->get('archer-to-merge');

            if (null === $base || null === $toMerge) {
                throw new \InvalidArgumentException('Les archers à fusionner sont obligatoires');
            }

            $base = $this->archerRepository->find($base);

            if (null === $base) {
                throw new \InvalidArgumentException("L'archer de destination n'existe pas");
            }

            $toMerge = $this->archerRepository->find($toMerge);

            if (null === $toMerge) {
                throw new \InvalidArgumentException('L\'archer à fusionner n\'existe pas');
            }

            $this->archerService->merge($base, $toMerge);

            $this->addFlash('success', 'Les archers ont été fusionnés');

            $redirectUrl = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($redirectUrl);
        }

        $archers = $this->archerRepository->findAll();

        return $this->render('admin/archers/merge.html.twig', [
            'archers' => $archers,
        ]);
    }

    /**
     * @internal
     */
    #[AdminRoute(
        path: '/download-contact-from-ffta',
        name: 'admin_download_contact_from_ffta',
    )]
    public function downloadContactFromFfta(AdminContext $context): Response
    {
        $season = ArcheryService::getCurrentSeason();
        $referer = $context->getReferrer() ?: $this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl();

        try {
            $this->fftaDirigeantService->connect();

            $licensesFromFFTA = $this->fftaDirigeantService->downloadLicencesFftaCsv($season);
        } catch (HttpExceptionInterface|TransportExceptionInterface $httpException) {
            $this->addFlash('danger', $httpException->getMessage());

            return $this->redirect($referer);
        }

        $licensesFromFFTA = explode("\n", $licensesFromFFTA);

        // On supprime la première ligne qui contient les titres des colonnes
        unset($licensesFromFFTA[0]);

        $csvHeaders = [
            'Name Prefix',
            'First Name',
            'Last Name',
            'Name Suffix',
            'Phonetic First',
            'Name Phonetic',
            'Middle Name',
            'Phonetic Last Name',
            'Nickname',
            'File As',
            'E-mail 1 - Label',
            'E-mail 1 - Value',
            'E-mail 2 - Label',
            'E-mail 2 - Value',
            'E-mail 3 - Label',
            'E-mail 3 - Value',
            'E-mail 4 - Label',
            'E-mail 4 - Value',
            'Phone 1 - Label',
            'Phone 1 - Value',
            'Phone 2 - Label',
            'Phone 2 - Value',
            'Phone 3 - Label',
            'Phone 3 - Value',
            'Phone 4 - Label',
            'Phone 4 - Value',
            'Address 1 - Label',
            'Address 1 - Country',
            'Address 1 - Street',
            'Address 1 - Extended Address',
            'Address 1 - City',
            'Address 1 - Region',
            'Address 1 - Postal Code',
            'Address 1 - PO Box',
            'Organization Name',
            'Organization Title',
            'Organization Department',
            'Birthday',
            'Custom Field 1 - Label',
            'Custom Field 1 - Value',
            'Custom Field 2 - Label',
            'Custom Field 2 - Value',
            'Notes',
            'Labels',
        ];

        $csvRows = [];

        foreach ($licensesFromFFTA as $licenseFromFFTA) {
            $license = str_getcsv($licenseFromFFTA, ';');

            if (empty($license[0])) {
                continue;
            }

            $csvRow = [
                'Name Prefix' => $license[1],
                'First Name' => $license[3],
                'Middle Name' => '',
                'Last Name' => $license[2],
                'Name Suffix' => '',
                'Phonetic First' => '',
                'Name Phonetic' => '',
                'Phonetic Last Name' => '',
                'Nickname' => '',
                'File As' => '',
                'E-mail 1 - Label' => 'Mail',
                'E-mail 1 - Value' => $license[9],
                'E-mail 2 - Label' => 'Mail Pro',
                'E-mail 2 - Value' => $license[10],
                'E-mail 3 - Label' => 'Responsable Légal 1',
                'E-mail 3 - Value' => $license[56],
                'E-mail 4 - Label' => 'Responsable Légal 2',
                'E-mail 4 - Value' => $license[60],
                'Phone 1 - Label' => 'Téléphone',
                'Phone 1 - Value' => $license[7],
                'Phone 2 - Label' => 'Mobile',
                'Phone 2 - Value' => $license[8],
                'Phone 3 - Label' => 'Responsable Légal 1',
                'Phone 3 - Value' => $license[55],
                'Phone 4 - Label' => 'Responsable Légal 2',
                'Phone 4 - Value' => $license[59],
                'Address 1 - Label' => 'Domicile',
                'Address 1 - Country' => 'France',
                'Address 1 - Street' => $license[14],
                'Address 1 - Extended Address' => '',
                'Address 1 - City' => $license[16],
                'Address 1 - Region' => '',
                'Address 1 - Postal Code' => $license[15],
                'Address 1 - PO Box' => '',
                'Organization Name' => $license[41],
                'Organization Title' => '',
                'Organization Department' => $license[17],
                'Birthday' => $license[4],
                'Custom Field 1 - Label' => 'Numéro de licence',
                'Custom Field 1 - Value' => $license[0],
                'Custom Field 2 - Label' => 'Catégorie âge',
                'Custom Field 2 - Value' => $license[26],
                'Notes' => '',
                'Labels' => '',
            ];

            $diff = array_diff(array_keys($csvRow), $csvHeaders);

            if ([] !== $diff) {
                $diff = implode(', ', $diff);
                $this->addFlash('danger', 'Les colonnes du fichier CSV ne correspondent pas aux colonnes du fichier Google Contact : '.$diff);

                return $this->redirect($referer);
            }

            $csvRows[] = array_values($csvRow);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="contacts_ffta.csv"');

        $output = fopen('php://temp', 'rb+');

        if (false === $output) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier temporaire");
        }

        // Add UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Write headers
        fputcsv($output, $csvHeaders);

        // Write data rows
        foreach ($csvRows as $row) {
            fputcsv($output, $row);
        }

        rewind($output);

        $response->setContent(stream_get_contents($output));

        fclose($output);

        return $response;
    }
}
