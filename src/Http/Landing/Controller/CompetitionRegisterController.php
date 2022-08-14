<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Competition\Form\CompetitionRegisterDepartureTargetArcherForm;
use App\Domain\Competition\Manager\CompetitionRegisterManager;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Infrastructure\Exception\InvalidSubmitCompetitionRegisterException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class CompetitionRegisterController extends AbstractController
{
    public const ROUTE_LANDING_COMPETITION_REGISTER = 'landing_competition_register';
    public const ROUTE_LANDING_COMPETITION_REGISTER_ARCHER = 'landing_competition_register_archer';
    public const ROUTE_LANDING_COMPETITION_REGISTER_DEPARTURE = 'landing_competition_register_departure';
    public const ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED = 'landing_competition_register_validated';
    public const ROUTE_LANDING_COMPETITION_REGISTER_REGISTRATION_RATE = 'landing_competition_register_registration_rate';
    public const ROUTE_LANDING_COMPETITION_REGISTER_LIST_OF_REGISTRANTS = 'landing_competition_register_list_of_registrants';

    public const SESSION_KEY_COMPETITION_REGISTER = 'competition_register';

    public function __construct(
        readonly private SerializerInterface $serializer,
        readonly private CompetitionRegisterManager $competitionRegisterManager
    ) {
    }

    private function deserializeRegisterArcher(Session $session): CompetitionRegisterDepartureTargetArcher
    {
        if ($session->has(self::SESSION_KEY_COMPETITION_REGISTER)) {
            return $this->serializer->deserialize(
                $session->get(self::SESSION_KEY_COMPETITION_REGISTER),
                CompetitionRegisterDepartureTargetArcher::class,
                'json'
            );
        }

        return new CompetitionRegisterDepartureTargetArcher();
    }

    private function serializeRegisterArcher(Session $session, CompetitionRegisterDepartureTargetArcher $registerDepartureTargetArcher): void
    {
        $session->set(self::SESSION_KEY_COMPETITION_REGISTER, $this->serializer->serialize($registerDepartureTargetArcher, 'json'));
    }

    #[Route('/inscription-concours/{slug}', name: self::ROUTE_LANDING_COMPETITION_REGISTER)]
    public function base(Session $session, CompetitionRegister $competitionRegister): Response
    {
        $session->remove(self::SESSION_KEY_COMPETITION_REGISTER);

        return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER, ['slug' => $competitionRegister->getSlug()]);
    }

    #[Route('/inscription-concours/{slug}/archer', name: self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER)]
    public function archer(Request $request, Session $session, CompetitionRegister $competitionRegister): Response
    {
        $register = $this->deserializeRegisterArcher($session);

        $form = $this->createForm(CompetitionRegisterDepartureTargetArcherForm::class, $register);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->serializeRegisterArcher($session, $register);

            return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_DEPARTURE, ['slug' => $competitionRegister->getSlug()]);
        }

        return $this->render('/landing/competition-registers/archer.html.twig', [
            'competitionRegister' => $competitionRegister,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/inscription-concours/{slug}/tir', name: self::ROUTE_LANDING_COMPETITION_REGISTER_DEPARTURE)]
    public function departure(Request $request, Session $session, CompetitionRegister $competitionRegister): Response
    {
        if (!$session->has(self::SESSION_KEY_COMPETITION_REGISTER)) {
            return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER, ['slug' => $competitionRegister->getSlug()]);
        }

        $register = $this->deserializeRegisterArcher($session);

        $form = $this->createForm(CompetitionRegisterDepartureTargetArcherForm::class, $register, [
            'competitionRegister' => $competitionRegister,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $recap = $this->competitionRegisterManager->handleSubmitForm($form, $competitionRegister, $register);

                $session->remove(self::SESSION_KEY_COMPETITION_REGISTER);

                return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED, [
                    'slug' => $competitionRegister->getSlug(),
                    'departures' => $recap,
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

    #[Route('/inscription-concours/{slug}/inscription-validee', name: self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED)]
    public function recap(CompetitionRegister $competitionRegister, Request $request): Response
    {
        if (!($request->query->all()['departures'] ?? [])) {
            return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_DEPARTURE, [
                'slug' => $competitionRegister->getSlug(),
            ]);
        }

        return $this->render('/landing/competition-registers/validated.html.twig', [
            'competitionRegister' => $competitionRegister,
            'departures' => $request->query->all()['departures'] ?? [],
        ]);
    }

    #[Route('/inscription-concours/{slug}/taux-inscription', name: self::ROUTE_LANDING_COMPETITION_REGISTER_REGISTRATION_RATE)]
    public function registrationRate(CompetitionRegister $competitionRegister): Response
    {
        return $this->render('/landing/competition-registers/registration-rate.html.twig', [
            'competitionRegister' => $competitionRegister,
        ]);
    }

    #[Route('/inscription-concours/{slug}/liste-des-inscrits', name: self::ROUTE_LANDING_COMPETITION_REGISTER_LIST_OF_REGISTRANTS)]
    public function list(CompetitionRegister $competitionRegister): Response
    {
        return $this->render('/landing/competition-registers/list-of-registrants.html.twig', [
            'competitionRegister' => $competitionRegister,
        ]);
    }
}
