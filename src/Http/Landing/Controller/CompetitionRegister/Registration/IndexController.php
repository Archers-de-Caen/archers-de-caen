<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Registration;

use App\Domain\Competition\Model\CompetitionRegister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    '/inscription-concours/{slug}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class IndexController extends AbstractController
{
    public const string ROUTE = 'landing_competition_register';

    public function __invoke(
        Session $session,
        CompetitionRegister $competitionRegister
    ): Response {
        $session->remove(SessionService::SESSION_KEY_COMPETITION_REGISTER);

        return $this->redirectToRoute(ArcherController::ROUTE, [
            'slug' => $competitionRegister->getSlug(),
        ]);
    }
}
