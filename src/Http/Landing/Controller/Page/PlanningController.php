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
    path: '/planning',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
final class PlanningController extends AbstractController
{
    public const string ROUTE = 'landing_planning';

    public function __construct(
        private readonly DataRepository $dataRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $plannings = $this->dataRepository->findByCode(Data::CODE_PLANNING)?->getContent() ?? [];

        $template = '/landing/club/planning.html.twig';

        if ($request->query->has('iframe')) {
            $template = '/landing/club/planning-iframe.html.twig';
        }

        return $this->render($template, [
            'plannings' => $plannings,
        ]);
    }
}
