<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\File\Config\DocumentType;
use App\Domain\File\Model\Document;
use App\Domain\File\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewspaperController extends AbstractController
{
    public const ROUTE_LANDING_NEWSPAPERS = 'route_landing_newspapers';
    public const ROUTE_LANDING_NEWSPAPER = 'route_landing_newspaper';

    #[Route('/gazettes', name: self::ROUTE_LANDING_NEWSPAPERS)]
    public function newspapers(DocumentRepository $documentRepository): Response
    {
        return $this->render('/landing/newspapers/newspapers.html.twig', [
            'newspapers' => $documentRepository->findBy([
                'type' => DocumentType::NEWSPAPER->value,
            ], [
                'createdAt' => 'DESC',
            ]),
        ]);
    }

    #[Route('/gazette/{documentName}', name: self::ROUTE_LANDING_NEWSPAPER)]
    public function newspaper(Document $document): Response
    {
        return $this->render('/landing/newspapers/newspapers.html.twig', [
            'document' => $document,
        ]);
    }
}
