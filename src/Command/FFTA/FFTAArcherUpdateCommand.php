<?php

declare(strict_types=1);

namespace App\Command\FFTA;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\ArcherLicense;
use App\Domain\Archer\Model\License;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Archer\Repository\LicenseRepository;
use App\Infrastructure\Service\ArcheryService;
use App\Infrastructure\Service\FFTA\FFTADirigeantService;
use App\Infrastructure\Service\FFTA\LicenseDTO;
use Doctrine\ORM\EntityManagerInterface;

use function Sentry\captureMessage;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Met à jour les archers licencié depuis le site de la FFTA',
)]
final readonly class FFTAArcherUpdateCommand
{
    public const string COMMAND_NAME = 'app:ffta:archer-update';

    public function __construct(
        private EntityManagerInterface $em,
        private ArcherRepository $archerRepository,
        private LicenseRepository $licenseRepository,
        private FFTADirigeantService $fftaDirigeantService
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->info('Run '.self::COMMAND_NAME);

        $season = ArcheryService::getCurrentSeason();

        // On se connecte à l'espace dirigeant
        $io->info('Connexion à l\'espace dirigeant');

        try {
            $this->fftaDirigeantService->connect();

            $io->info('Récupération des licences');

            $licensesFromFFTA = $this->fftaDirigeantService->getLicenses($season);

            $io->info(\count($licensesFromFFTA).' licences récupéré');
        } catch (HttpExceptionInterface|TransportExceptionInterface $httpException) {
            $io->error($httpException->getMessage());

            captureMessage($httpException->getMessage());

            return Command::FAILURE;
        }

        $archers = $this->reformatArchersArray($this->archerRepository->findAll());
        $licenses = $this->licenseRepository->findAll();
        foreach ($licensesFromFFTA as $licenseFromFFTA) {
            try {
                $archer = $this->getArcher($archers, $licenseFromFFTA);
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());

                captureMessage($e->getMessage());

                break;
            }

            $licenseType = str_replace('"', '', $licenseFromFFTA->getLicenseType() ?? '');

            if (!$archer->getArcherLicenseActive() instanceof ArcherLicense) {
                $license = array_filter(
                    $licenses,
                    static fn (License $license): bool => strtolower($licenseType) === strtolower($license->getTitle() ?? '')
                );

                if ([] === $license) {
                    $msg = 'License not found for '.$licenseType;

                    $io->error($msg);

                    captureMessage($msg);

                    return Command::FAILURE;
                }

                try {
                    $io->info('Nouvelle licence: '.json_encode($licenseFromFFTA->toArray(), \JSON_THROW_ON_ERROR));
                } catch (\JsonException) {
                    $io->info('Nouvelle licence: impossible d\'encodé $newLicense');
                }

                try {
                    $gender = match ($licenseFromFFTA->getGender()) {
                        Gender::MAN => 'Homme',
                        Gender::WOMAN => 'Femme',
                        Gender::OTHER, Gender::UNDEFINED, null => throw new \RuntimeException('To be implemented'),
                    };

                    $category = str_replace('é', 'e', $licenseFromFFTA->getCategory() ?? '');
                    $category = Category::createFromString($category.' '.$gender);
                } catch (\ValueError $e) {
                    $io->error($e->getMessage());

                    captureMessage($e->getMessage());

                    $category = null;
                }

                $archer->addArcherLicense(
                    (new ArcherLicense())
                        ->setActive(true)
                        ->setDateStart($licenseFromFFTA->getLicenseDateStart())
                        ->setDateEnd($licenseFromFFTA->getLicenseDateEnd() ?? new \DateTime('31-12-'.$season))
                        ->setLicense($license[array_key_first($license)])
                        ->setCategory($category)
                );
            }
        }

        $io->info('Flush');
        $this->em->flush();
        $io->success('finish '.self::COMMAND_NAME);

        return Command::SUCCESS;
    }

    /**
     * @param array<Archer> $archers
     *
     * @return array<string, Archer>
     */
    private function reformatArchersArray(array $archers): array
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
     * @throws \RuntimeException Si pas de numéro de licence fourni
     */
    private function getArcher(array &$archers, LicenseDTO $archerData): Archer
    {
        if ($archerData->getLicense() && isset($archers[$archerData->getLicense()])) {
            return $archers[$archerData->getLicense()];
        }

        if ($archerData->getLicense()) {
            $phone = null;
            if ($archerData->getPhone()) {
                $phone = str_replace([' ', '-', '.'], '', $archerData->getPhone());
                $phone = substr($phone, 0, 12);
            }

            $archer = (new Archer())
                ->setFirstName($archerData->getFirstName())
                ->setLastName($archerData->getLastName())
                ->setLicenseNumber($archerData->getLicense())
                ->setPhone($phone)
                ->setEmail($archerData->getEmail())
                ->setGender($archerData->getGender())
            ;

            $this->em->persist($archer);

            $archers[$archer->getLicenseNumber()] = $archer;

            return $archer;
        }

        throw new \RuntimeException('Licence not found');
    }
}
