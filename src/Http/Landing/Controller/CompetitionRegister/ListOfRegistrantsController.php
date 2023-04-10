<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister;

use App\Domain\Competition\Model\CompetitionRegister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/inscription-concours/{slug}/liste-des-inscrits',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class ListOfRegistrantsController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_list_of_registrants';

    public function __invoke(CompetitionRegister $competitionRegister): Response
    {
        return $this->render('/landing/competition-registers/list-of-registrants.html.twig', [
            'competitionRegister' => $competitionRegister,
        ]);
    }
}
