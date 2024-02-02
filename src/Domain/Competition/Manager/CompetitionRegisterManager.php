<?php

declare(strict_types=1);

namespace App\Domain\Competition\Manager;

use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDeparture;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTarget;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher as Registration;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository as RegistrationRepository;
use App\Infrastructure\Exception\InvalidSubmitCompetitionRegisterException;
use App\Infrastructure\Mailing\EmailRenderingException;
use App\Infrastructure\Mailing\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mime\Address;

use function Symfony\Component\Translation\t;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CompetitionRegisterManager
{
    public function __construct(
        readonly private Environment $environment,
        readonly private EntityManagerInterface $em,
        readonly private Mailer $mailer,
        readonly private LoggerInterface $logger,
        readonly private ArcherRepository $archerRepository,
        private readonly RegistrationRepository $competitionRegisterDepartureTargetArcherRepository,
    ) {
    }

    /**
     * Créer une actualité depuis un formulaire d'inscription au concours.
     * L'actualité est créée en brouillon, elle doit être persist et flush.
     */
    public function createActuality(CompetitionRegister $competitionRegister): Page
    {
        try {
            $html = $this->environment->render('landing/competition-registers/actuality.html.twig', [
                'competitionRegister' => $competitionRegister,
            ]);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            $this->logger->error($e);

            $html = 'Une erreur est survenue';
        }

        return (new Page())
            ->setCategory(Category::ACTUALITY)
            ->setTitle('Inscription au concours des Archers de Caen')
            ->setContent($html)
        ;
    }

    /**
     * @throws InvalidSubmitCompetitionRegisterException
     */
    public function handleSubmitForm(
        FormInterface $form,
        CompetitionRegister $competitionRegister,
        Registration $register
    ): void {
        $recap = [];

        $departures = $competitionRegister
            ->getDepartures()
            ->filter(static fn(CompetitionRegisterDeparture $crd): bool => $crd->getRegistration() <= $crd->getMaxRegistration());

        $firstRegistration = null;

        foreach ($departures as $departure) {
            /** @var ?CompetitionRegisterDepartureTarget $target */
            $target = $form->get($departure->getId().'-targets')->getData();
            if ($target) {
                $register->setTarget($target);
                $clonedRegister = clone $register;

                if (!$firstRegistration instanceof Registration) {
                    $firstRegistration = $clonedRegister;
                }

                $this->em->persist($clonedRegister);

                $recap[] = $departure->getDate()?->format('d/m/Y à H:i').' à '.$target->getDistance().'m sur '.($target->getType()?->value ? t($target->getType()->value, domain: 'competition') : null);
            }
        }

        /** @var string $licenseNumber */
        $licenseNumber = $register->getLicenseNumber();
        $archer = $this->archerRepository->findOneBy(['licenseNumber' => $licenseNumber]);
        $isArcherDeCaen = $archer && $archer->getArcherLicenseActive();
        $registrations = $this->competitionRegisterDepartureTargetArcherRepository
            ->findByCompetitionRegisterAndLicenseNumber($competitionRegister, $licenseNumber);
        $alreadyPaid = \count(array_filter($registrations, static fn (Registration $crdta): bool => $crdta->isPaid()));

        if ($firstRegistration instanceof Registration && $isArcherDeCaen && !$alreadyPaid) {
            $firstRegistration->setPaid(true);
        }

        if (!$recap) {
            throw new InvalidSubmitCompetitionRegisterException('Veuillez sélectionner au moins un départ');
        }

        $this->em->flush();

        $this->sendEmailToParticipant($register, $recap);
        $this->sendEmailToCompetitionOwner($register, $recap);
    }

    private function sendEmailToParticipant(Registration $register, array $recap): void
    {
        if (!$register->getEmail()) {
            return;
        }

        try {
            $email = $this->mailer
                ->createEmail('/mails/competition-register/confirmation-participant.twig', [
                    'recap' => $recap,
                    'register' => $register,
                ]);
        } catch (EmailRenderingException) {
            return;
        }

        $email
            ->subject('Votre inscription à notre concours à été prise en compte')
            ->addTo(new Address($register->getEmail(), $register->getFirstName().' '.$register->getLastName()));

        $this->mailer->send($email);
    }

    private function sendEmailToCompetitionOwner(Registration $register, array $recap): void
    {
        if (!$register->getEmail()) {
            return;
        }

        try {
            $email = $this->mailer
                ->createEmail('/mails/competition-register/register-owner-notification.twig', [
                    'recap' => $recap,
                    'register' => $register,
                ]);
        } catch (EmailRenderingException $emailRenderingException) {
            return;
        }

        $email
            ->subject('Nouvelle inscription au concours')
            ->addTo(new Address('inscription-concours@archers-caen.fr', 'Inscription concours'));

        $this->mailer->send($email);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function allRegistrationsIsSameArcher(array $registrations): bool
    {
        $registrations = array_values($registrations);

        if ($registrations === []) {
            return false;
        }

        $firstRegistration = $registrations[0];

        $licenseNumber = $firstRegistration->getLicenseNumber();
        $competition = $firstRegistration->getTarget()?->getDeparture()?->getCompetitionRegister();

        if (!$competition || !$licenseNumber) {
            throw new \InvalidArgumentException();
        }

        foreach ($registrations as $registration) {
            if ($licenseNumber !== $registration->getLicenseNumber()) {
                throw new \InvalidArgumentException();
            }

            if ($competition !== $registration->getTarget()?->getDeparture()?->getCompetitionRegister()) {
                throw new \InvalidArgumentException();
            }
        }

        return true;
    }
}
