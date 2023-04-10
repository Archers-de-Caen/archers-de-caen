<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Payment;

use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher as Registration;
use App\Domain\Competition\Manager\CompetitionRegisterPayment;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository as RegistrationRepository;
use App\Http\Landing\Controller\CompetitionRegister\RecapController;
use App\Http\Landing\Controller\CompetitionRegister\Registration\DepartureController;
use App\Http\Landing\Controller\CompetitionRegisterController;
use Helloasso\Exception\HelloassoException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/inscription-concours/{slug}/paiement/{licenseNumber}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class PaymentController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_payment';

    public function __invoke(
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

            return $this->redirectToRoute(DepartureController::ROUTE, [
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

        return $this->redirectToRoute(RecapController::ROUTE, [
            'slug' => $competitionRegister->getSlug(),
            'licenseNumber' => $licenseNumber,
        ]);
    }
}
