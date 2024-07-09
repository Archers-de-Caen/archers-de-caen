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
    path: '/equipe-de-direction',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
final class TeamManagementController extends AbstractController
{
    public const string ROUTE = 'landing_management_team';

    public function __invoke(DataRepository $dataRepository): Response
    {
        return $this->render('/landing/club/management-team.html.twig', [
            'managementTeams' => $dataRepository->findByCode(Data::CODE_MANAGEMENT_TEAM)?->getContent(),
        ]);
    }
}
