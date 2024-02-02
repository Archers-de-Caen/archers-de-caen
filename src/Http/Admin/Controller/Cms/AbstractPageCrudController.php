<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Cms;

use Symfony\Component\Translation\TranslatableMessage;
use App\Domain\Cms\Admin\Field\CKEditorField;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\File\Admin\Field\PhotoField;
use App\Domain\File\Form\PhotoFormType;
use App\Domain\Newsletter\NewsletterType;
use App\Http\Admin\Controller\DashboardController;
use App\Infrastructure\LiipImagine\CacheResolveMessage;
use App\Infrastructure\Mailing\ActualityNewsletterMessage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

class AbstractPageCrudController extends AbstractCrudController
{
    public function __construct(
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly MessageBusInterface $bus,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    #[\Override]
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->select('entity')
            ->addSelect('tags')
            ->addSelect('image')

            ->leftJoin('entity.tags', 'tags')
            ->leftJoin('entity.image', 'image')
        ;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setHelp(Crud::PAGE_NEW, 'Le rendu final peut-être différent de l\'éditeur')
            ->setHelp(Crud::PAGE_EDIT, 'Le rendu final peut-être différent de l\'éditeur')

            ->setPageTitle(Crud::PAGE_DETAIL, fn (Page $page): string => (string) $page)

            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');

        $createdAt = DateTimeField::new('createdAt')
            ->setLabel('Date de création');

        $title = TextField::new('title')
            ->setLabel('Titre');

        $content = CKEditorField::new('content');

        $status = ChoiceField::new('status')
            ->setLabel('Statut')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Status::class,
                'choice_label' => fn (Status $choice): TranslatableMessage => t($choice->value, domain: 'page'),
                'choices' => Status::cases(),
            ])
            ->formatValue(fn ($value, ?Page $entity): TranslatableMessage|string => !$value || !$entity instanceof Page || !$entity->getStatus() instanceof Status ? '' : t($entity->getStatus()->value, domain: 'page'))
        ;

        $image = PhotoField::new('image')
            ->setLabel('Image')
            ->setFormType(PhotoFormType::class)
            ->setRequired(false)
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $status, $image, $createdAt];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $status, $image, $content, $createdAt];
        }

        return [$title, $image, $content];
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('category')->setChoices(Category::cases()))
            ->add(ChoiceFilter::new('status')->setChoices(Status::cases()))
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        parent::configureActions($actions);

        $publish = Action::new('publish')
            ->setLabel('Publier')
            ->linkToCrudAction('publish')
            ->displayIf(static fn (Page $page): bool => Status::DRAFT === $page->getStatus())
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $publish);
    }

    public function publish(
        MessageBusInterface $messageBus,
        AdminContext $context,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        /** @var Page $entity */
        $entity = $context->getEntity()->getInstance();

        $entity->publish();

        $em->flush();

        if (Category::ACTUALITY === $entity->getCategory() && $entity->getId()) {
            $messageBus->dispatch(new ActualityNewsletterMessage($entity->getId(), NewsletterType::ACTUALITY_NEW));
        }

        return $this->redirect($context->getReferrer() ?: $urlGenerator->generate(DashboardController::ROUTE));
    }

    protected function dispatchCache(Page $entityInstance): void
    {
        if (!$entityInstance->getImage()) {
            return;
        }
        if (!$entityInstance->getImage()->getImageName()) {
            return;
        }
        $this->bus->dispatch(new CacheResolveMessage($entityInstance->getImage()->getImageName()));
    }
}
