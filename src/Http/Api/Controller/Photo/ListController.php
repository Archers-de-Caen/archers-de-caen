<?php

declare(strict_types=1);

namespace App\Http\Api\Controller\Photo;

use App\Domain\Archer\Model\Archer;
use App\Domain\File\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[Route(
    path: '/photos',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
#[IsGranted(Archer::ROLE_ADMIN, message: 'only admin', statusCode: Response::HTTP_FORBIDDEN)]
final class ListController extends AbstractController
{
    public const string ROUTE = 'photos_list';

    public function __construct(
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $galleryId = $request->query->get('gallery');

        if (null === $galleryId) {
            return $this->json(
                data: ['message' => 'Gallery id is required'],
                status: Response::HTTP_BAD_REQUEST
            );
        }

        $photos = $this->photoRepository->findBy(['gallery' => $galleryId]);

        return $this->json(
            data: $photos,
            status: Response::HTTP_OK,
            context: ['groups' => ['Photo', 'Timestamp', 'Token']]
        );
    }
}
