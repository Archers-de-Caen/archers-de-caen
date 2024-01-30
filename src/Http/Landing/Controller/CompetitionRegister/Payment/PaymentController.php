<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Payment;

use App\Domain\Competition\Manager\CompetitionRegisterPayment;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository as RegistrationRepository;
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
class PaymentController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_payment';

    public function __invoke(
        CompetitionRegister $competitionRegister,
        string $licenseNumber,
        RegistrationRepository $competitionRegisterDepartureTargetArcherRepository,
        CompetitionRegisterPayment $competitionRegisterPayment,
        LoggerInterface $logger
    ): Response {
        return $this->redirect('https://www.helloasso.com/associations/archers-de-caen/evenements/concours-salle-2023-caen');
    }
}
