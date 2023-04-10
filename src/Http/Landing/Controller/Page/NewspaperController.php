<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\File\Config\DocumentType;
use App\Domain\File\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/gazettes',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class NewspaperController extends AbstractController
{
    public const ROUTE = 'route_landing_newspapers';

    public function __invoke(DocumentRepository $documentRepository): Response
    {
        return $this->render('/landing/newspapers/newspapers.html.twig', [
            'newspapers' => $documentRepository->findBy([
                'type' => DocumentType::NEWSPAPER->value,
            ], [
                'createdAt' => 'DESC',
            ]),
        ]);
    }
}
