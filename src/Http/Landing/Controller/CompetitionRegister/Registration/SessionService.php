<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Registration;

use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher as Registration;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\SerializerInterface;

class SessionService
{
    public const SESSION_KEY_COMPETITION_REGISTER = 'competition_register';

    public function __construct(
        readonly private SerializerInterface $serializer
    ) {
    }

    public function deserializeRegisterArcher(Session $session): Registration
    {
        if ($session->has(self::SESSION_KEY_COMPETITION_REGISTER)) {
            return $this->serializer->deserialize(
                $session->get(self::SESSION_KEY_COMPETITION_REGISTER),
                Registration::class,
                'json'
            );
        }

        return new Registration();
    }

    public function serializeRegisterArcher(Session $session, Registration $registerDepartureTargetArcher): void
    {
        $session->set(
            self::SESSION_KEY_COMPETITION_REGISTER,
            $this->serializer->serialize($registerDepartureTargetArcher, 'json')
        );
    }
}
