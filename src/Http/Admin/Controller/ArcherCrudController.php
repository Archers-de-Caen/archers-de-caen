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

final class ArcherCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ArcherRepository $archerRepository,
        private readonly ArcherService $archerService,
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

        return $actions
            ->add(Crud::PAGE_INDEX, $impersonation)
            ->add(Crud::PAGE_INDEX, $mergeArchers);
    }

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
}
