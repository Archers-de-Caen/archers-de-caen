<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister;

use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository as RegistrationRepository;
use App\Http\Landing\Controller\CompetitionRegister\Registration\DepartureController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/inscription-concours/{slug}/inscription-validee/{licenseNumber}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class RecapController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_validated';

    public function __invoke(
        RegistrationRepository $competitionRegisterDepartureTargetArcherRepository,
        CompetitionRegister $competitionRegister,
        string $licenseNumber
    ): Response {
        $registrations = $competitionRegisterDepartureTargetArcherRepository
            ->findByCompetitionRegisterAndLicenseNumber($competitionRegister, $licenseNumber);

        if (!$registrations) {
            return $this->redirectToRoute(DepartureController::ROUTE, [
                'slug' => $competitionRegister->getSlug(),
            ]);
        }

        return $this->render('/landing/competition-registers/validated.html.twig', [
            'competitionRegister' => $competitionRegister,
            'registrations' => $registrations,
        ]);
    }
}
