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
    path: '/equipe-de-direction',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class TeamManagementController extends AbstractController
{
    public const ROUTE = 'landing_management_team';

    public function __invoke(DataRepository $dataRepository): Response
    {
        return $this->render('/landing/club/management-team.html.twig', [
            'managementTeams' => $dataRepository->findOneBy(['code' => 'MANAGEMENT_TEAM'])?->getContent(),
        ]);
    }
}
