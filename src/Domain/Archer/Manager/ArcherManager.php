<?php

declare(strict_types=1);

namespace App\Domain\Archer\Manager;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository;
use App\Infrastructure\Service\Anonymize;

class ArcherManager
{
    public function __construct(
        readonly private CompetitionRegisterDepartureTargetArcherRepository $targetArcherRepository,
        readonly private ArcherRepository $archerRepository
    ) {
    }

    public function findArcherFromLicense(string $licenseNumber): CompetitionRegisterDepartureTargetArcher|Archer|null
    {
        if ($archer = $this->targetArcherRepository->findOneArcherByLicenseNumber($licenseNumber)) {
            return $archer;
        }

        if ($archer = $this->archerRepository->findOneBy(['licenseNumber' => $licenseNumber])) {
            return $archer;
        }

        return null;
    }

    public function generateAnonymizeArray(CompetitionRegisterDepartureTargetArcher|Archer $archer): array
    {
        return [
            'licenseNumber' => $archer->getLicenseNumber(),
            'firstName' => $archer->getFirstName(),
            'lastName' => $archer->getLastName(),
            'email' => $archer->getEmail() ? Anonymize::email($archer->getEmail()) : null,
            'phone' => $archer->getPhone() ? Anonymize::phone($archer->getPhone()) : null,
            'gender' => $archer->getGender(),
            'category' => $archer->getCategory(),
            'club' => $archer instanceof Archer ? 'Archers de Caen' : $archer->getClub(),
        ];
    }
}
