<?php

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
final class CreateArcher extends Command
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $em,
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
                if ($details['hidden'] ?? false) {
                    $value = $io->askHidden($details['sentence']);
                } elseif (!empty($details['choices'])) {
                    $value = $io->choice($details['sentence'], $details['choices']);
                } else {
                    $value = $io->ask($details['sentence']);
                }

                $properties[$property]['value'] = $value ? strval($value) : null;

                $violations = $this->validator->validatePropertyValue(Archer::class, $property, $properties[$property]['value']);

                if ($violations->count()) {

                    /** @var ConstraintViolationInterface $violation */
                    foreach ($violations as $violation) {
                        $io->error($violation->getMessage());
                    }
                }
            } while ($violations->count());
        }

        $archer->setFirstName($properties['firstName']['value']);
        $archer->setLastName($properties['lastName']['value']);
        $archer->setEmail($properties['email']['value']);
        $archer->setPhone($properties['phone']['value']);
        $archer->setPlainPassword($properties['plainPassword']['value']);
        $archer->setLicenseNumber($properties['licenseNumber']['value']);
        $archer->addRole($properties['role']['value']);

        $violations = $this->validator->validate($archer);

        if ($violations->count()) {

            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $io->error($violation->getPropertyPath() . ' : ' . $violation->getMessage());
            }

            return self::FAILURE;
        }

        $this->em->persist($archer);

        $this->em->flush();

        return self::SUCCESS;
    }
}