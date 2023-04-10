<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Registration;

use App\Domain\Competition\Form\CompetitionRegisterDepartureTargetArcherForm;
use App\Domain\Competition\Model\CompetitionRegister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    '/inscription-concours/{slug}/archer',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST
    ]
)]
class ArcherController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_archer';

    public function __invoke(
        Request $request,
        Session $session,
        CompetitionRegister $competitionRegister,
        SessionService $sessionService
    ): Response {
        $register = $sessionService->deserializeRegisterArcher($session);

        $form = $this->createForm(CompetitionRegisterDepartureTargetArcherForm::class, $register);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sessionService->serializeRegisterArcher($session, $register);

            return $this->redirectToRoute(DepartureController::ROUTE, [
                'slug' => $competitionRegister->getSlug()
            ]);
        }

        return $this->render('/landing/competition-registers/archer.html.twig', [
            'competitionRegister' => $competitionRegister,
            'form' => $form->createView(),
        ]);
    }
}
