<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Gallery;

use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Repository\GalleryRepository;
use App\Helper\PaginatorHelper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/galeries',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
class GalleriesController extends AbstractController
{
    public const ROUTE = 'landing_galleries';

    public function __invoke(Request $request, GalleryRepository $galleryRepository): Response
    {
        $currentPage = ((int) $request->query->get('page') ?: 1) - 1;
        $elementByPage = 16;

        $galleries = new Paginator(
            $galleryRepository->createQueryBuilder('gallery')
                ->where('gallery.status = :status')
                ->setParameter('status', Status::PUBLISH->value)
                ->orderBy('gallery.createdAt', 'DESC')
                ->setFirstResult($currentPage * $elementByPage)
                ->setMaxResults($elementByPage) // 24, car sur un écran 1080p la dernière ligne est complete
        );

        return $this->render('/landing/galleries/galleries.html.twig', [
            'galleries' => $galleries,
            'paginator' => PaginatorHelper::pagination($currentPage + 1, (int) ceil($galleries->count() / $elementByPage)),
        ]);
    }
}
