<?php

declare(strict_types=1);

namespace App\Http\Api\Controller\Photo;

use App\Domain\Archer\Model\Archer;
use App\Domain\File\Model\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/photos/{token}',
    name: self::ROUTE,
    methods: Request::METHOD_DELETE
)]
class DeleteController extends AbstractController
{
    public const ROUTE = 'api_photos_delete';

    public function __invoke(Photo $photo, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isGranted(Archer::ROLE_ADMIN)) {
            return $this->json([
                'message' => 'Vous devez être administrateur pour supprimer cette photo !',
                'messageCode' => 'only_admin',
            ], Response::HTTP_FORBIDDEN);
        }

        $em->remove($photo);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
