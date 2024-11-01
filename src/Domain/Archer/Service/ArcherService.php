<?php

declare(strict_types=1);

namespace App\Domain\Archer\Service;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;

readonly class ArcherService
{
    public function __construct(
        private ArcherRepository $archerRepository,
    ) {
    }

    /**
     * Fusionne l'archer "toMerge" dans l'archer "base".
     *
     * @throws \InvalidArgumentException
     */
    public function merge(Archer $base, Archer $toMerge): Archer
    {
        if ($base->getId() === $toMerge->getId()) {
            throw new \InvalidArgumentException('Cannot merge the same archer');
        }

        $base->setFirstName($base->getFirstName() ?? $toMerge->getFirstName());
        $base->setLastName($base->getLastName() ?? $toMerge->getLastName());
        $base->setEmail($base->getEmail() ?? $toMerge->getEmail());
        $base->setGender($base->getGender() ?? $toMerge->getGender());
        $base->setCategory($base->getCategory() ?? $toMerge->getCategory());
        $base->setLicenseNumber($base->getLicenseNumber() ?? $toMerge->getLicenseNumber());
        $base->setCreatedAt($base->getCreatedAt() ?? $toMerge->getCreatedAt());
        $base->setLastLogin($base->getLastLogin() ?? $toMerge->getLastLogin());
        $base->setNewsletters($base->getNewsletters() === [] ? $toMerge->getNewsletters() : $base->getNewsletters());
        $base->setRoles($base->getRoles() === [] ? $toMerge->getRoles() : $base->getRoles());
        $base->setPassword($base->getPassword() ?? $toMerge->getPassword());

        foreach ($toMerge->getResults() as $result) {
            $base->addResult($result);
        }

        $toMerge->getResults()->clear();

        foreach ($toMerge->getResultsTeams() as $resultTeam) {
            $base->addResultTeam($resultTeam);
        }

        $toMerge->getResultsTeams()->clear();

        foreach ($toMerge->getArcherLicenses() as $archerLicense) {
            $base->addArcherLicense($archerLicense);
        }

        $toMerge->getArcherLicenses()->clear();

        $this->archerRepository->save($base, true);
        $this->archerRepository->remove($toMerge, true);

        return $base;
    }
}
