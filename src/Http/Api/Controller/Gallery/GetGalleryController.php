<?php

declare(strict_types=1);

namespace App\Http\Api\Controller\Gallery;

use App\Domain\Cms\Model\Gallery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/galleries/{id}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class GetGalleryController extends AbstractController
{
    public const ROUTE = 'api_gallery_get';

    public function __invoke(Gallery $gallery): JsonResponse
    {
        return $this->json($gallery, context: ['groups' => Gallery::SERIALIZER_GROUP_SHOW, 'Timestamp']);
    }
}
