<?php

declare(strict_types=1);

namespace App\Http\Api\Controller;

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
use Symfony\Component\Routing\Annotation\Route;

final class PhotoApiController extends AbstractController
{
    #[Route('/photos', name: 'photos_upload', methods: Request::METHOD_POST)]
    public function upload(Request $request, EntityManagerInterface $em): JsonResponse
    {
        if (!$this->isGranted(Archer::ROLE_ADMIN)) {
            return $this->json('only admin', Response::HTTP_FORBIDDEN);
        }

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

    #[Route('/photos/{token}', name: 'photos_delete', methods: Request::METHOD_DELETE)]
    public function delete(Photo $photo, EntityManagerInterface $em): JsonResponse
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
