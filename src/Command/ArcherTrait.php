<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Archer\Model\Archer;
use Exception;

trait ArcherTrait
{
    /**
     * @param array<Archer> $archers
     *
     * @return array<string, Archer>
     */
    public function reformatArchersArray(array $archers): array
    {
        $archersReformatted = [];

        foreach ($archers as $archer) {
            if ($archer->getLicenseNumber()) {
                $archersReformatted[$archer->getLicenseNumber()] = $archer;
            }
        }

        return $archersReformatted;
    }

    /**
     * @throws Exception Si pas de numéro de licence fourni
     */
    public function getArcher(array &$archers, ?string $license, string $name): Archer
    {
        if ($license && isset($archers[$license])) {
            $archer = $archers[$license];
        } elseif ($license) {
            $archerName = explode(' ', trim($name), 2);

            $archer = (new Archer())
                ->setFirstName($archerName[0])
                ->setLastName($archerName[1] ?? '')
                ->setLicenseNumber($license);

            $this->em->persist($archer);

            $archers[$archer->getLicenseNumber()] = $archer;
        } else {
            throw new Exception('Licence not found');
        }

        return $archer;
    }
}
