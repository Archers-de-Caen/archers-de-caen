<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\File\Config\DocumentType;
use App\Domain\File\Repository\DocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/gazettes',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
final class NewspaperController extends AbstractController
{
    public const string ROUTE = 'landing_newspapers';

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
