<?php

declare(strict_types=1);

namespace App\Domain\Competition\Manager;

use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher as Registration;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository as RegistrationRepository;
use App\Http\Landing\Controller\CompetitionRegister\Payment\EndController;
use App\Http\Landing\Controller\CompetitionRegister\Payment\ErrorController;
use App\Http\Landing\Controller\CompetitionRegister\RecapController;
use Helloasso\Exception\HelloassoException;
use Helloasso\HelloassoClient;
use Helloasso\Models\Carts\CheckoutPayer;
use Helloasso\Models\Carts\InitCheckoutBody;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class CompetitionRegisterPayment
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ParameterBagInterface $parameterBag,
        private RegistrationRepository $competitionRegisterDepartureTargetArcherRepository,
        private ArcherRepository $archerRepository,
        private CompetitionRegisterManager $competitionRegisterManager,
    ) {
    }

    /**
     * @param array<CompetitionRegisterDepartureTargetArcher> $registrations
     *
     * @throws HelloassoException
     * @throws \InvalidArgumentException
     */
    public function generatePaymentLink(array $registrations): string
    {
        $registrations = array_values($registrations);

        $this->competitionRegisterManager->allRegistrationsIsSameArcher($registrations);

        $firstRegistration = $registrations[0];

        /** @var string $licenseNumber */
        $licenseNumber = $firstRegistration->getLicenseNumber();

        /** @var CompetitionRegister $competition */
        $competition = $firstRegistration->getTarget()?->getDeparture()?->getCompetitionRegister();

        $urlParameters = ['slug' => $competition->getSlug(), 'licenseNumber' => $licenseNumber];

        $backUrl = $this->urlGenerator->generate(
            RecapController::ROUTE,
            $urlParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $errorUrl = $this->urlGenerator->generate(
            ErrorController::ROUTE,
            $urlParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $endUrl = $this->urlGenerator->generate(
            EndController::ROUTE,
            $urlParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $parameter = $this->parameterBag->all();
        $helloasso = new HelloassoClient(
            $parameter['hello_asso_id'],
            $parameter['hello_asso_secret'],
            $parameter['hello_asso_organization_name'],
            true
        );

        $amountToPaid = $this->getAmountToPaid($competition, $licenseNumber);

        if ($amountToPaid <= 0) {
            throw new \InvalidArgumentException('Vous n\'avez rien à payer');
        }

        $checkoutIntent = (new InitCheckoutBody())
            ->setTotalAmount($amountToPaid)
            ->setInitialAmount($amountToPaid)
            ->setItemName(\count($registrations).' départ(s) pour le concours des Archers de Caen')
            ->setBackUrl($backUrl)
            ->setErrorUrl($errorUrl)
            ->setReturnUrl($endUrl)
            ->setPayer(
                (new CheckoutPayer())
                    ->setFirstName($firstRegistration->getFirstName())
                    ->setLastName($firstRegistration->getLastName())
                    ->setEmail($firstRegistration->getEmail())
            )
            ->setMetadata([
                'registrations' => array_map(
                    static fn (CompetitionRegisterDepartureTargetArcher $registration): ?string => $registration->getId()?->__toString(),
                    $registrations
                ),
            ])
        ;

        return $helloasso->checkout->create($checkoutIntent)->getRedirectUrl();
    }

    public function getAmountToPaid(
        CompetitionRegister $competitionRegister,
        string $licenseNumber
    ): int {
        $registrations = $this->competitionRegisterDepartureTargetArcherRepository
            ->findByCompetitionRegisterAndLicenseNumber($competitionRegister, $licenseNumber);

        $this->competitionRegisterManager->allRegistrationsIsSameArcher($registrations);

        $archer = $this->archerRepository->findOneBy(['licenseNumber' => $licenseNumber]);

        $isArcherDeCaen = $archer && $archer->getArcherLicenseActive();

        $firstRegistration = $registrations[0];

        $alreadyPaid = \count(array_filter($registrations, static fn (Registration $crdta): bool => $crdta->isPaid()));

        $departureToPaid = \count($registrations) - $alreadyPaid;

        if ($departureToPaid <= 0) {
            return 0;
        }

        if (!$alreadyPaid && $isArcherDeCaen) {
            --$departureToPaid;
        }

        if ($departureToPaid <= 0) {
            return 0;
        }

        if (!$alreadyPaid && $firstRegistration->getCategory()?->isAdult()) {
            return ($departureToPaid * 5 + ($isArcherDeCaen ? 0 : 3)) * 100;
        }

        return ($departureToPaid * 5) * 100;
    }
}
