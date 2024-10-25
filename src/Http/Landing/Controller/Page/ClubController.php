<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Cms\Model\Data;
use App\Domain\Cms\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/club',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
final class ClubController extends AbstractController
{
    public const string ROUTE = 'landing_club';

    public function __construct(
        private readonly DataRepository $dataRepository
    ) {
    }

    public function __invoke(): Response
    {
        $faqs = $this->dataRepository->findByCode(Data::CODE_FAQ);
        $plannings = $this->dataRepository->findByCode(Data::CODE_PLANNING)?->getContent() ?? [];

        return $this->render('/landing/club/index.html.twig', [
            'faqs' => $faqs,
            'plannings' => $plannings,
        ]);
    }
}
