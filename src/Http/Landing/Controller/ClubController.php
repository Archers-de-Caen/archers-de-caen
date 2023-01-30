<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ClubController extends AbstractController
{
    public const ROUTE_LANDING_CLUB = 'landing_club';
    public const ROUTE_LANDING_PLANNING = 'landing_planning';
    public const ROUTE_LANDING_MANAGEMENT_TEAM = 'landing_management_team';

    #[Route('/club', name: self::ROUTE_LANDING_CLUB)]
    public function index(DataRepository $dataRepository): Response
    {
        $faqs = $dataRepository->findOneBy(['code' => 'FAQ']);

        return $this->render('/landing/club/index.html.twig', [
            'faqs' => $faqs,
        ]);
    }

    #[Route('/planning', name: self::ROUTE_LANDING_PLANNING)]
    public function planning(Request $request): Response
    {
        if ($request->query->has('iframe')) {
            return $this->render('/landing/club/planning-iframe.html.twig');
        }

        return $this->render('/landing/club/planning.html.twig');
    }

    #[Route('/equipe-de-direction', name: self::ROUTE_LANDING_MANAGEMENT_TEAM)]
    public function managementTeam(DataRepository $dataRepository): Response
    {
        return $this->render('/landing/club/management-team.html.twig', [
            'managementTeams' => $dataRepository->findOneBy(['code' => 'MANAGEMENT_TEAM'])?->getContent(),
        ]);
    }
}
