<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Cms\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/club',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class ClubController extends AbstractController
{
    public const ROUTE = 'landing_club';

    public function __invoke(DataRepository $dataRepository): Response
    {
        $faqs = $dataRepository->findOneBy(['code' => 'FAQ']);

        return $this->render('/landing/club/index.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
