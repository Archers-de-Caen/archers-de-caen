<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller;

use App\Domain\Cms\Admin\Field\GalleryField;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Gallery;
use App\Domain\File\Admin\Field\PhotoField;
use App\Domain\File\Model\Photo;
use App\Domain\Newsletter\NewsletterType;
use App\Http\Landing\Controller\Gallery\GalleryController;
use App\Infrastructure\LiipImagine\CacheResolveMessage;
use App\Infrastructure\Mailing\GalleryNewsletterMessage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function Symfony\Component\Translation\t;

use Symfony\Component\Translation\TranslatableMessage;

final class GalleryCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Gallery::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('form/gallery.html.twig')

            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        parent::configureActions($actions);

        $publish = Action::new('publish')
            ->setLabel('Publier')
            ->linkToCrudAction('publish')
            ->displayIf(static fn (Gallery $gallery): bool => Status::DRAFT === $gallery->getStatus())
        ;

        $publicLink = Action::new('public-link')
            ->setLabel('Lien public')
            ->linkToUrl(function (Gallery $gallery): string {
                return $this->urlGenerator->generate(GalleryController::ROUTE, [
                    'slug' => $gallery->getSlug(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $publish)
            ->add(Crud::PAGE_INDEX, $publicLink);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $title = TextField::new('title')
            ->setLabel('Titre');
        $mainPhoto = PhotoField::new('mainPhoto')
            ->setLabel('Image principale')
            ->setRequired(false)
        ;
        $gallery = GalleryField::new('photos');

        $status = ChoiceField::new('status')
            ->setLabel('Statut')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions([
                'class' => Status::class,
                'choice_label' => static fn (Status $choice): TranslatableMessage => t($choice->value, domain: 'page'),
                'choices' => Status::cases(),
            ])
            ->formatValue(static fn ($value, ?Gallery $entity): TranslatableMessage|string => !$value || !$entity instanceof Gallery || !$entity->getStatus() instanceof Status ? '' : t($entity->getStatus()->value, domain: 'page'))
        ;

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $title, $status, $mainPhoto];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $title, $status, $mainPhoto, $gallery];
        }

        return [$title, $mainPhoto, $gallery];
    }

    /**
     * @param Gallery $entityInstance
     */
    #[\Override]
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        $this->dispatchCache($entityInstance);
    }

    /**
     * @param Gallery $entityInstance
     */
    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);

        $this->dispatchCache($entityInstance);
    }

    private function dispatchCache(Gallery $entityInstance): void
    {
        if ($entityInstance->getMainPhoto() instanceof Photo && $entityInstance->getMainPhoto()->getImageName()) {
            $this->bus->dispatch(new CacheResolveMessage($entityInstance->getMainPhoto()->getImageName()));
        }

        $this->bus->dispatch(new CacheResolveMessage($entityInstance->getPhotos()->map(static fn ($photo): ?string => $photo->getImageName())->toArray()));
    }

    public function publish(
        MessageBusInterface $messageBus,
        AdminContext $context,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        /** @var Gallery $entity */
        $entity = $context->getEntity()->getInstance();

        $entity->publish();

        $em->flush();

        if ($entity->getId()) {
            $messageBus->dispatch(new GalleryNewsletterMessage($entity->getId(), NewsletterType::GALLERY_NEW));
        }

        return $this->redirect($context->getReferrer() ?: $urlGenerator->generate(DashboardController::ROUTE));
    }
}
