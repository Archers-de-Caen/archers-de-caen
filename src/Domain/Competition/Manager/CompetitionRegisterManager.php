<?php

declare(strict_types=1);

namespace App\Domain\Competition\Manager;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDeparture;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTarget;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Infrastructure\Exception\InvalidSubmitCompetitionRegisterException;
use App\Infrastructure\Mailing\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mime\Address;
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
     * @return array<string>
     *
     * @throws InvalidSubmitCompetitionRegisterException
     */
    public function handleSubmitForm(FormInterface $form, CompetitionRegister $competitionRegister, CompetitionRegisterDepartureTargetArcher $register): array
    {
        $recap = [];

        $departures = $competitionRegister
            ->getDepartures()
            ->filter(fn (CompetitionRegisterDeparture $crd) => $crd->getRegistration() <= $crd->getMaxRegistration());

        foreach ($departures as $departure) {
            /** @var ?CompetitionRegisterDepartureTarget $target */
            $target = $form->get($departure->getId().'-targets')->getData();
            if ($target) {
                $register->setTarget($target);

                $this->em->persist(clone $register);

                $recap[] = $departure->getDate()?->format('d/m/Y à H:i').' à '.$target->getDistance().'m, sur un blason '.$target->getType()?->toString();
            }
        }

        if (!$recap) {
            throw new InvalidSubmitCompetitionRegisterException('Veuillez sélectionner au moins un départ');
        }

        $this->em->flush();

        $this->sendEmailToParticipant($register, $recap);
        $this->sendEmailToCompetitionOwner($register, $recap);

        return $recap;
    }

    private function sendEmailToParticipant(CompetitionRegisterDepartureTargetArcher $register, array $recap): void
    {
        if (!$register->getEmail()) {
            return;
        }

        $email = $this->mailer
            ->createEmail('/mails/competition-register/confirmation-participant.twig', [
                'recap' => $recap,
                'register' => $register,
            ]);

        if (!$email) {
            return;
        }

        $email
            ->subject('Votre inscription à notre concours à été prise en compte')
            ->addTo(new Address($register->getEmail(), $register->getFirstName().' '.$register->getLastName()));

        $this->mailer->send($email);
    }

    private function sendEmailToCompetitionOwner(CompetitionRegisterDepartureTargetArcher $register, array $recap): void
    {
        if (!$register->getEmail()) {
            return;
        }

        $email = $this->mailer
            ->createEmail('/mails/competition-register/register-owner-notification.twig', [
                'recap' => $recap,
                'register' => $register,
            ]);

        if (!$email) {
            return;
        }

        $email
            ->subject('Nouvelle inscription au concours')
            ->addTo(new Address('inscription-concours@archers-caen.fr', 'Inscription concours'));

        $this->mailer->send($email);
    }
}
