<?php

declare(strict_types=1);

namespace App\Domain\File\Service;

use App\Domain\File\Message\NewspaperAccessCreatedMessage;
use App\Domain\File\Model\NewspaperAccess;
use App\Domain\File\Repository\NewspaperAccessRepository;
use App\Helper\SecurityHelper;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class NewspaperAccessService
{
    public function __construct(
        private NewspaperAccessRepository $newspaperAccessRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function getNewspaperAccessByPassword(string $password): ?NewspaperAccess
    {
        return $this->newspaperAccessRepository->findOneBy([
            'password' => $password,
        ]);
    }

    /**
     * @throws \Exception
     * @throws ExceptionInterface
     */
    public function createNewspaperAccess(string $email): NewspaperAccess
    {
        $newspaperAccess = (new NewspaperAccess())
            ->setEmail($email)
            ->setPassword(SecurityHelper::generateRandomToken(16));

        $this->newspaperAccessRepository->save($newspaperAccess, true);

        $this->messageBus->dispatch(new NewspaperAccessCreatedMessage(
            email: $newspaperAccess->getEmail() ?? '',
            password: $newspaperAccess->getPassword() ?? '',
        ));

        return $newspaperAccess;
    }
}
