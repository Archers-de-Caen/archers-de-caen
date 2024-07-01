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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:ffta:archer-update',
    description: 'Met à jour les archers licencié depuis le site de la FFTA',
)]
final class FFTAArcherUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ArcherRepository $archerRepository,
        private readonly LicenseRepository $licenseRepository,
        private readonly FFTADirigeantService $fftaDirigeantService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Run '.$this->getName());

        $season = ArcheryService::getCurrentSeason();

        // On se connecte à l'espace dirigeant
        $io->info('Connexion à l\'espace dirigeant');
        try {
            $this->fftaDirigeantService->connect();

            $io->info('Récupération des licences');

            $newLicenses = $this->fftaDirigeantService->getLicenses($season);

            $io->info(\count($newLicenses).' licences récupéré');
        } catch (HttpExceptionInterface|TransportExceptionInterface $httpException) {
            $io->error($httpException->getMessage());

            captureMessage($httpException->getMessage());

            return Command::FAILURE;
        }

        $archers = $this->reformatArchersArray($this->archerRepository->findAll());
        $licenses = $this->licenseRepository->findAll();

        foreach ($newLicenses as $newLicense) {
            try {
                $archer = $this->getArcher($archers, $newLicense);
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());

                captureMessage($e->getMessage());

                break;
            }

            $licenseType = str_replace('"', '', $newLicense->getLicenseType() ?? '');

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
                    $io->info('Nouvelle licence: '.json_encode($newLicense, \JSON_THROW_ON_ERROR));
                } catch (\JsonException) {
                    $io->info('Nouvelle licence: impossible d\'encodé $newLicense');
                }

                try {
                    $gender = match ($newLicense->getGender()) {
                        Gender::MAN => 'Homme',
                        Gender::WOMAN => 'Femme',
                        Gender::OTHER, Gender::UNDEFINED, null => throw new \RuntimeException('To be implemented'),
                    };

                    $category = Category::createFromString($newLicense->getCategory().' '.$gender);
                } catch (\ValueError $e) {
                    $io->error($e->getMessage());

                    captureMessage($e->getMessage());

                    $category = null;
                }

                $archer->addArcherLicense(
                    (new ArcherLicense())
                        ->setActive(true)
                        ->setDateStart($newLicense->getLicenseDateStart())
                        ->setDateEnd($newLicense->getLicenseDateEnd() ?? new \DateTime('31-12-'.$season))
                        ->setLicense($license[array_key_first($license)])
                        ->setCategory($category)
                );
            }
        }

        $io->info('Flush');

        $this->em->flush();

        $io->success('finish '.$this->getName());

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
                // Todo: Gérer les doublons
                // ->setEmail($archerData->getEmail())
                ->setGender($archerData->getGender())
            ;

            $this->em->persist($archer);

            $archers[$archer->getLicenseNumber()] = $archer;

            return $archer;
        }

        throw new \RuntimeException('Licence not found');
    }
}
