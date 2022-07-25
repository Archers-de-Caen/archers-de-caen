<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Competition\Form\CompetitionRegisterDepartureTargetArcherForm;
use App\Domain\Competition\Model\CompetitionRegister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CompetitionRegisterController extends AbstractController
{
    public const ROUTE_LANDING_COMPETITION_REGISTER = 'landing_competition_register';

    #[Route('/inscription-concours/{slug}', name: self::ROUTE_LANDING_COMPETITION_REGISTER)]
    public function resultsArrow(Request $request, CompetitionRegister $competitionRegister): Response
    {
        $form = $this->createForm(CompetitionRegisterDepartureTargetArcherForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->render('landing/competition-registers/competition-register.html.twig', [
            'competitionRegister' => $competitionRegister,
            'form' => $form->createView(),
        ]);
    }
}
