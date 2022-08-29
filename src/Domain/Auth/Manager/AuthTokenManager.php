<?php

declare(strict_types=1);

namespace App\Domain\Auth\Manager;

use App\Domain\Archer\Model\Archer;
use App\Domain\Auth\Model\AuthToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher;
use Symfony\Component\String\ByteString;

class AuthTokenManager
{
    private NativePasswordHasher $hasher;

    public function __construct(readonly private EntityManagerInterface $em)
    {
        $this->hasher = new NativePasswordHasher();
    }

    public function create(Archer $archer): AuthToken
    {

        $token = $this->generateToken();

        $authToken = (new AuthToken())
            ->setArcher($archer)
            ->setPlainToken($token)
            ->setToken($this->hasher->hash($token))
            ->setExpiredAt((new \DateTimeImmutable())->add(new \DateInterval('PT15M')))
        ;

        $this->em->persist($authToken);

        return $authToken;
    }

    public function generateToken(int $blockNumber = 3, int $lengthByBlock = 4, string $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ'): string
    {
        $token = '';

        for ($i = 0; $i < $blockNumber; $i++) {
            $token .= ByteString::fromRandom($lengthByBlock, $alphabet)->toString() . ($i < ($blockNumber - 1) ? '-' : '');
        }

        return $token;
    }

    public function verifyToken(AuthToken $authToken, string $plainToken): bool
    {
        if (!$authToken->getToken()) {
            return false;
        }

        return $this->hasher->verify($authToken->getToken(), $plainToken);
    }
}
