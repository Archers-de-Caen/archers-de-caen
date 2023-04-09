<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Repository\GalleryRepository;
use App\Domain\File\Repository\PhotoRepository;
use App\Helper\PaginatorHelper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GalleryController extends AbstractController
{
    public const ROUTE_LANDING_GALLERIES = 'landing_galleries';
    public const ROUTE_LANDING_GALLERY = 'landing_gallery';

    #[Route('/galeries', name: self::ROUTE_LANDING_GALLERIES)]
    public function galleries(Request $request, GalleryRepository $galleryRepository): Response
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

    #[Route('/galerie/{slug}', name: self::ROUTE_LANDING_GALLERY)]
    public function gallery(Request $request, Gallery $gallery, PhotoRepository $photoRepository): Response
    {
        $currentPage = ((int) $request->query->get('page') ?: 1) - 1;
        $elementByPage = 16;

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
