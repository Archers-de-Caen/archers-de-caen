<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister;

use App\Domain\Competition\Model\CompetitionRegister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/inscription-concours/{slug}/taux-inscription',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class RegistrationRateController extends AbstractController
{
    public const string ROUTE = 'landing_competition_register_registration_rate';

    public function __invoke(CompetitionRegister $competitionRegister): Response
    {
        return $this->render('/landing/competition-registers/registration-rate.html.twig', [
            'competitionRegister' => $competitionRegister,
        ]);
    }
}
