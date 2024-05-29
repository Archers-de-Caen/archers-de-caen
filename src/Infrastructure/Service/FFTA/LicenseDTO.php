<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Config\Gender;

final readonly class LicenseDTO
{
    public function __construct(
        private ?string $license,
        private ?string $firstName,
        private ?string $lastName,
        private ?Gender $gender,
        private ?string $phone,
        private ?string $email,
        private ?string $location,
        private ?string $status,
        private ?\DateTimeInterface $licenseDateStart,
        private ?\DateTimeInterface $licenseDateEnd,
        private ?string $licenseType,
        private ?string $category,
    ) {
    }

    /**
     * @return LicenseDTO[]
     */
    public static function createListFromCsv(string $licenses): array
    {
        /*
         * 0:'"Code adhérent"', 1:'Civilité', 2:'Nom',3:'Prénom', 4:'DDN', 5:'Nationalité', 6:'"Pays de naissance"',
         * 7:'Téléphone', 8:'Mobile', 9:'Mail', 10:'"Mail Pro"', 11:'"Droit à l\'image"', 12:'"Lettre d\'informations"',
         * 13:'"Offres commerciales"', 14:'Adresse', 15:'"Code postal"', 16:'Commune', 17:'"Type licence"', 18:'État',
         * 19:'"Saisie par"', 20:'IA', 21:'Photo', 22:'Saison', 23:'"Saison première licence"', 25:'Discipline(s)',
         * 25:'"Catégorie âge sportif"', 26:'"Catégorie âge"', 27:'Mutations',
         * 28:'Surclassement',29:'"Date de demande"',30:'"Date compta"',31:'"Date de début"',32:'"Date de fin de validité"',
         * 33:'Honorabilité',34:'"Montant licence"',35:'"Options licence"',36:'"Montant options"',37:'"Type certificat"',
         * 38:'"Date de début certificat médical"',39:'"Date de fin certificat médical"',40:'"Code structure"',41:'"Nom structure"',
         * 42:'"Nom court structure"',43:'"Adresse structure"',44:'"Commune structure"',45:'"Code postal structure"',
         * 46:'"Mail structure"',47:'"Téléphone structure"',48:'"Mobile structure"',49:'"Code Comité Départemental"',
         * 50:'"Nom Comité Départemental"',51:'"Code Comité Régional"',52:'"Nom Comité Régional"',53:'"Nom du responsable légal"',
         * 54:'"Prénom du responsable légal"',55:'"Téléphone du responsable légal"',56:'"Mail du responsable légal"',
         * 57:'"Nom du responsable légal secondaire"',58:'"Prénom du responsable légal secondaire"',
         * 59:'"Téléphone du responsable légal secondaire"',60:'"Mail du responsable légal secondaire"',
         */

        $rows = explode("\n", $licenses);

        // On supprime la première ligne qui contient les titres des colonnes
        unset($rows[0]);

        $formattedLicenses = [];

        foreach ($rows as $row) {
            $license = str_getcsv($row, ';');

            if (empty($license[0])) {
                continue;
            }

            $formattedLicenses[] = self::createFromCsvRow($license);
        }

        return $formattedLicenses;
    }

    public static function createFromCsvRow(array $license): self
    {
        return new self(
            license: $license[0],
            firstName: $license[2],
            lastName: $license[3],
            gender: $license[1] ? Gender::createFromString($license[1]) : null,
            phone: $license[8],
            email: $license[9],
            location: sprintf('%s, %s %s', $license[14], $license[15], $license[16]),
            status: $license[18],
            licenseDateStart: \DateTime::createFromFormat('Y-m-d', $license[31]) ?: null,
            licenseDateEnd: \DateTime::createFromFormat('Y-m-d', $license[33]) ?: null,
            licenseType: $license[18],
            category: $license[26],
        );
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getLicenseDateStart(): ?\DateTimeInterface
    {
        return $this->licenseDateStart;
    }

    public function getLicenseDateEnd(): ?\DateTimeInterface
    {
        return $this->licenseDateEnd;
    }

    public function getLicenseType(): ?string
    {
        return $this->licenseType;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}
