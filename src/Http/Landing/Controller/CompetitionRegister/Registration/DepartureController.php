<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Registration;

use App\Domain\Competition\Form\CompetitionRegisterDepartureTargetArcherForm;
use App\Domain\Competition\Manager\CompetitionRegisterManager;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Http\Landing\Controller\CompetitionRegister\RecapController;
use App\Infrastructure\Exception\InvalidSubmitCompetitionRegisterException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    '/inscription-concours/{slug}/tir',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST
    ]
)]
class DepartureController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_departure';

    public function __invoke(
        Request $request,
        Session $session,
        CompetitionRegister $competitionRegister,
        CompetitionRegisterManager $competitionRegisterManager,
        SessionService $sessionService
    ): Response {
        if (!$session->has(SessionService::SESSION_KEY_COMPETITION_REGISTER)) {
            return $this->redirectToRoute(ArcherController::ROUTE, [
                'slug' => $competitionRegister->getSlug()
            ]);
        }

        $register = $sessionService->deserializeRegisterArcher($session);

        $form = $this->createForm(CompetitionRegisterDepartureTargetArcherForm::class, $register, [
            'competitionRegister' => $competitionRegister,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $competitionRegisterManager->handleSubmitForm($form, $competitionRegister, $register);

                $session->remove(SessionService::SESSION_KEY_COMPETITION_REGISTER);

                return $this->redirectToRoute(RecapController::ROUTE, [
                    'slug' => $competitionRegister->getSlug(),
                    'licenseNumber' => $register->getLicenseNumber(),
                ]);
            } catch (InvalidSubmitCompetitionRegisterException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('/landing/competition-registers/departure.html.twig', [
            'competitionRegister' => $competitionRegister,
            'form' => $form->createView(),
        ]);
    }
}
