<?php

declare(strict_types=1);

namespace App\Command\Cron;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\ArcherLicense;
use App\Domain\Archer\Model\License;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Archer\Repository\LicenseRepository;
use App\Infrastructure\Service\FFTA\FFTAService;
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
        private readonly FFTAService $fftaService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Run '.$this->getName());

        $season = (int) date('m') < 9 ? date('Y') : (string) ((int) date('Y') + 1);

        // On se connecte à l'espace dirigeant
        $io->info('Connexion à l\'espace dirigeant');
        try {
            $this->fftaService->connect();

            $io->info('Récupération des licences');

            $newLicenses = $this->fftaService->getLicenses($season);

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
                        ->setDateEnd($newLicense->getLicenseDateEnd())
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
            $archer = (new Archer())
                ->setFirstName($archerData->getFirstName())
                ->setLastName($archerData->getLastName())
                ->setLicenseNumber($archerData->getLicense())
                ->setPhone($archerData->getPhone())
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
