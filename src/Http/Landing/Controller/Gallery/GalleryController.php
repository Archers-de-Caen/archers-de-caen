<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Gallery;

use App\Domain\Cms\Model\Gallery;
use App\Domain\File\Repository\PhotoRepository;
use App\Helper\PaginatorHelper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/galerie/{slug}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class GalleryController extends AbstractController
{
    public const ROUTE = 'landing_gallery';

    public function __invoke(Request $request, Gallery $gallery, PhotoRepository $photoRepository): Response
    {
        $currentPage = ((int) $request->query->get('page') ?: 1) - 1;
        $elementByPage = 24;

        $photos = new Paginator(
            $photoRepository->createQueryBuilder('photo')
                ->where('photo.gallery = :gallery')
                ->setParameter('gallery', $gallery->getId(), 'uuid')
                ->setFirstResult($currentPage * $elementByPage)
                ->setMaxResults($elementByPage) // 24, car sur un écran 1080p la dernière ligne est complete);
        );

        return $this->render('/landing/galleries/gallery.html.twig', [
            'gallery' => $gallery,
            'photos' => $photos,
            'paginator' => PaginatorHelper::pagination($currentPage + 1, (int) ceil($photos->count() / $elementByPage)),
        ]);
    }
}
