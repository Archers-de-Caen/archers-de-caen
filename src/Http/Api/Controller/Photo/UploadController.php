<?php

declare(strict_types=1);

namespace App\Http\Api\Controller\Photo;

use App\Domain\Archer\Model\Archer;
use App\Domain\File\Form\PhotoFormType;
use App\Domain\File\Model\Photo;
use App\Helper\FormHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    methods: Request::METHOD_POST
)]
#[IsGranted(Archer::ROLE_ADMIN, message: 'only admin', statusCode: Response::HTTP_FORBIDDEN)]
class UploadController extends AbstractController
{
    public const string ROUTE = 'photos_upload';

    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $photo = new Photo();

        /** @var UploadedFile $imageFile */
        $imageFile = $request->files->get('imageFile');
        $photo->setImageFile($imageFile);

        $form = $this->createForm(PhotoFormType::class, $photo, ['csrf_protection' => false]);
        $form->submit(null, false);

        if (!$form->isValid()) {
            return $this->json([
                'messageCode' => 'form_not_valid',
                'message' => 'Les informations fournis ne sont pas bonne',
                'errorDetails' => FormHelper::getErrorsArray($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $em->persist($photo);
            $em->flush();
        } catch (\Exception $e) {
            return $this->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($photo, Response::HTTP_CREATED, [], ['groups' => ['Photo', 'Timestamp', 'Token']]);
    }
}
