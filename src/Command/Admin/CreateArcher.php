<?php

declare(strict_types=1);

namespace App\Command\Admin;

use App\Domain\Archer\Model\Archer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:archers:create',
    description: 'Permet de créer un archer',
)]
class CreateArcher extends Command
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
        string $name = null
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $archer = new Archer();

        $properties = [
            'firstName' => [
                'value' => null,
                'sentence' => 'Prénom de l\'archer',
            ],
            'lastName' => [
                'value' => null,
                'sentence' => 'Nom de l\'archer',
            ],
            'email' => [
                'value' => null,
                'sentence' => 'Email de l\'archer',
            ],
            'phone' => [
                'value' => null,
                'sentence' => 'Téléphone de l\'archer',
            ],
            'plainPassword' => [
                'value' => null,
                'sentence' => 'Mot de passe de l\'archer (cacher)',
                'hidden' => true,
            ],
            'licenseNumber' => [
                'value' => null,
                'sentence' => 'Numéro de licence de l\'archer',
            ],
            'role' => [
                'value' => null,
                'sentence' => 'Liste des roles de l\'archer',
                'choices' => [
                    Archer::ROLE_ARCHER => 'Archer',
                    Archer::ROLE_EDITOR => 'Éditeur',
                    Archer::ROLE_ADMIN => 'Admin',
                    Archer::ROLE_DEVELOPER => 'Développeur',
                ],
            ],
        ];

        foreach ($properties as $property => $details) {
            do {
                /* @var string $value */
                if ($details['hidden'] ?? false) {
                    $value = $io->askHidden($details['sentence']);
                } elseif (!empty($details['choices'])) {
                    $value = $io->choice($details['sentence'], $details['choices']);
                } else {
                    $value = $io->ask($details['sentence']);
                }

                $properties[$property]['value'] = $value;

                $violations = $this->validator
                    ->validatePropertyValue(Archer::class, $property, $properties[$property]['value'])
                ;

                if ($violations->count()) {
                    /** @var ConstraintViolationInterface $violation */
                    foreach ($violations as $violation) {
                        /** @var string $message */
                        $message = $violation->getMessage();

                        $io->error($message);
                    }
                }
            } while ($violations->count());
        }

        if (($value = $properties['firstName']['value']) && \is_string($value)) {
            $archer->setFirstName($value);
        }

        if (($value = $properties['lastName']['value']) && \is_string($value)) {
            $archer->setLastName($value);
        }

        if (($value = $properties['email']['value']) && \is_string($value)) {
            $archer->setEmail($value);
        }

        if (($value = $properties['phone']['value']) && \is_string($value)) {
            $archer->setPhone($value);
        }

        if (($value = $properties['plainPassword']['value']) && \is_string($value)) {
            $archer->setPlainPassword($value);
        }

        if (($value = $properties['licenseNumber']['value']) && \is_string($value)) {
            $archer->setLicenseNumber($value);
        }

        if (($value = $properties['role']['value']) && \is_string($value)) {
            $archer->addRole($value);
        }

        $violations = $this->validator->validate($archer);

        if ($violations->count()) {
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $io->error($violation->getPropertyPath().' : '.$violation->getMessage());
            }

            return self::FAILURE;
        }

        $this->em->persist($archer);

        $this->em->flush();

        return self::SUCCESS;
    }
}
