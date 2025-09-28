<?php

declare(strict_types=1);

namespace App\Infrastructure\Seo;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\PageRepository;
use App\Http\Landing\Controller\Actuality\ActualityController;
use App\Http\Landing\Controller\Page\PageController;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PageRepository $pageRepository
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerCmsPagesUrls($event->getUrlContainer(), $event->getUrlGenerator());
    }

    public function registerCmsPagesUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        /** @var Page[] $pages */
        $pages = $this->pageRepository
            ->createQueryBuilder('page')
            ->where(\sprintf("page.status = '%s'", Status::PUBLISH->value))
            ->getQuery()
            ->getResult()
        ;

        foreach ($pages as $page) {
            $url = new UrlConcrete(
                $router->generate(
                    name: Category::PAGE === $page->getCategory() ? PageController::ROUTE : ActualityController::ROUTE,
                    parameters: ['slug' => $page->getSlug()],
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL
                )
            );

            $urls->addUrl($url, Category::PAGE === $page->getCategory() ? 'page' : 'actuality');
        }
    }
}
