<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GalleryController extends AbstractController
{
    #[Route('/galeries', name: 'landing_galleries')]
    public function galleries(GalleryRepository $galleryRepository): Response
    {
        return $this->render('/landing/galleries/galleries.html.twig', [
            'galleries' => $galleryRepository->findAll(),
        ]);
    }

    #[Route('/galerie/{slug}', name: 'landing_gallery')]
    public function gallery(Gallery $gallery): Response
    {
        return $this->render('/landing/galleries/gallery.html.twig', [
            'gallery' => $gallery,
        ]);
    }
}
