<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class GalleryController extends AbstractController
{
    public const ROUTE_LANDING_GALLERIES = 'landing_galleries';
    public const ROUTE_LANDING_GALLERY = 'landing_gallery';

    #[Route('/galeries', name: self::ROUTE_LANDING_GALLERIES)]
    public function galleries(GalleryRepository $galleryRepository): Response
    {
        return $this->render('/landing/galleries/galleries.html.twig', [
            'galleries' => $galleryRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/galerie/{slug}', name: self::ROUTE_LANDING_GALLERY)]
    public function gallery(Gallery $gallery): Response
    {
        return $this->render('/landing/galleries/gallery.html.twig', [
            'gallery' => $gallery,
        ]);
    }
}
