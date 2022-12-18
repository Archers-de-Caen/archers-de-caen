<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Competition\Form\CompetitionRegisterDepartureTargetArcherForm;
use App\Domain\Competition\Manager\CompetitionRegisterManager;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher as Registration;
use App\Domain\Competition\Manager\CompetitionRegisterPayment;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository as RegistrationRepository;
use App\Infrastructure\Exception\InvalidSubmitCompetitionRegisterException;
use Helloasso\Exception\HelloassoException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CompetitionRegisterController extends AbstractController
{
    public const ROUTE_LANDING_COMPETITION_REGISTER = 'landing_competition_register';
    public const ROUTE_LANDING_COMPETITION_REGISTER_ARCHER = 'landing_competition_register_archer';
    public const ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED = 'landing_competition_register_validated';
    public const ROUTE_LANDING_COMPETITION_REGISTER_REGISTRATION_RATE = 'landing_competition_register_registration_rate';
    public const ROUTE_LANDING_COMPETITION_REGISTER_LIST_OF_REGISTRANTS = 'landing_competition_register_list_of_registrants';

    public const ROUTE_LANDING_COMPETITION_REGISTER_PAYMENT = 'landing_competition_register_payment';
    public const ROUTE_LANDING_COMPETITION_REGISTER_PAYMENT_ERROR = 'landing_competition_register_payment_error';
    public const ROUTE_LANDING_COMPETITION_REGISTER_PAYMENT_END = 'landing_competition_register_payment_end';

    public function __construct(
        readonly private CompetitionRegisterManager $competitionRegisterManager
    ) {
    }

    #[Route(
        '/inscription-concours/{slug}',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER,
        methods: [Request::METHOD_GET]
    )]
    public function base(CompetitionRegister $competitionRegister): Response
    {
        return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER, [
            'slug' => $competitionRegister->getSlug()
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/archer',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER,
        methods: [Request::METHOD_GET, Request::METHOD_POST]
    )]
    public function archer(Request $request, CompetitionRegister $competitionRegister): Response
    {
        $register = new Registration();

        $form = $this->createForm(CompetitionRegisterDepartureTargetArcherForm::class, $register, [
            'competitionRegister' => $competitionRegister,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->competitionRegisterManager->handleSubmitForm($form, $competitionRegister, $register);

                return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED, [
                    'slug' => $competitionRegister->getSlug(),
                    'licenseNumber' => $register->getLicenseNumber(),
                ]);
            } catch (InvalidSubmitCompetitionRegisterException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('/landing/competition-registers/archer.html.twig', [
            'competitionRegister' => $competitionRegister,
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/inscription-validee/{licenseNumber}',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED,
        methods: [Request::METHOD_GET]
    )]
    public function recap(
        RegistrationRepository $competitionRegisterDepartureTargetArcherRepository,
        CompetitionRegister $competitionRegister,
        string $licenseNumber
    ): Response {
        $registrations = $competitionRegisterDepartureTargetArcherRepository
            ->findByCompetitionRegisterAndLicenseNumber($competitionRegister, $licenseNumber);

        if (!$registrations) {
            return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER, [
                'slug' => $competitionRegister->getSlug(),
            ]);
        }

        return $this->render('/landing/competition-registers/validated.html.twig', [
            'competitionRegister' => $competitionRegister,
            'registrations' => $registrations,
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/paiement/{licenseNumber}',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_PAYMENT,
        methods: [Request::METHOD_GET]
    )]
    public function paidByHelloAsso(
        CompetitionRegister $competitionRegister,
        string $licenseNumber,
        RegistrationRepository $competitionRegisterDepartureTargetArcherRepository,
        CompetitionRegisterPayment $competitionRegisterPayment,
        LoggerInterface $logger
    ): Response {
        $registrations = $competitionRegisterDepartureTargetArcherRepository
            ->findByCompetitionRegisterAndLicenseNumber($competitionRegister, $licenseNumber);

        $registrations = array_filter($registrations, static fn (Registration $crdta) => !$crdta->isPaid());

        if (!$registrations) {
            $this->addFlash('success', 'Vous n\'avez rien à payer');

            return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_ARCHER, [
                'slug' => $competitionRegister->getSlug(),
            ]);
        }

        try {
            return $this->redirect($competitionRegisterPayment->generatePaymentLink($registrations));
        } catch (HelloassoException $e) {
            $logger->error($e);

            $this->addFlash('danger', 'Une erreur est survenue avec notre prestataire de paiement');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED, [
            'slug' => $competitionRegister->getSlug(),
            'licenseNumber' => $licenseNumber,
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/paiement/{licenseNumber}/erreur',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_PAYMENT_ERROR
    )]
    public function paidByHelloAssoError(Request $request, string $slug, string $licenseNumber): Response
    {
        $this->addFlash(
            'danger',
            'Une erreur est survenue, votre paiement n\'a pas abouti: '.$request->query->get('error')
        );

        return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED, [
            'slug' => $slug,
            'licenseNumber' => $licenseNumber,
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/paiement/{licenseNumber}/fini',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_PAYMENT_END
    )]
    public function paidByHelloAssoBack(Request $request, string $slug, string $licenseNumber): Response
    {
        if ('succeeded' === $request->query->get('code')) {
            $this->addFlash('success', 'Nous avons bien reçu votre paiement');
        } else {
            $this->addFlash('danger', 'Une erreur est survenue, votre paiement n\'a pas abouti');
        }

        return $this->redirectToRoute(self::ROUTE_LANDING_COMPETITION_REGISTER_VALIDATED, [
            'slug' => $slug,
            'licenseNumber' => $licenseNumber,
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/taux-inscription',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_REGISTRATION_RATE
    )]
    public function registrationRate(CompetitionRegister $competitionRegister): Response
    {
        return $this->render('/landing/competition-registers/registration-rate.html.twig', [
            'competitionRegister' => $competitionRegister,
        ]);
    }

    #[Route(
        '/inscription-concours/{slug}/liste-des-inscrits',
        name: self::ROUTE_LANDING_COMPETITION_REGISTER_LIST_OF_REGISTRANTS
    )]
    public function list(CompetitionRegister $competitionRegister): Response
    {
        return $this->render('/landing/competition-registers/list-of-registrants.html.twig', [
            'competitionRegister' => $competitionRegister,
        ]);
    }
}
