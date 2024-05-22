<?php

declare(strict_types=1);

namespace App\Command\Cron;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\ArcherLicense;
use App\Domain\Archer\Model\License;
use App\Infrastructure\Service\FFTAService;
use Doctrine\ORM\EntityManagerInterface;

use function Sentry\captureMessage;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ffta:archer-update',
    description: 'Met à jour les archers licencié depuis le site de la FFTA',
)]
final class FFTAArcherUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
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
        $this->fftaService->connect();

        $io->info('Récupération des licences');
        $newLicenses = $this->fftaService->getLicenses($season);

        $io->info(\count($newLicenses).' licences récupéré');

        $archers = $this->reformatArchersArray($this->em->getRepository(Archer::class)->findAll());
        $licenses = $this->em->getRepository(License::class)->findAll();

        foreach ($newLicenses as $newLicense) {
            try {
                $archer = $this->getArcher($archers, $newLicense);
            } catch (\RuntimeException $e) {
                $io->error($e->getMessage());

                captureMessage($e->getMessage());

                break;
            }

            $licenseType = str_replace('"', '', $newLicense['licenseType']);

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
                    $gender = match ($newLicense['gender']) {
                        Gender::MAN => 'Homme',
                        Gender::WOMAN => 'Femme',
                        Gender::OTHER, Gender::UNDEFINED, null => throw new \RuntimeException('To be implemented'),
                    };

                    $category = Category::createFromString($newLicense['category'].' '.$gender);
                } catch (\ValueError $e) {
                    $io->error($e->getMessage());

                    captureMessage($e->getMessage());

                    $category = null;
                }

                $archer->addArcherLicense(
                    (new ArcherLicense())
                        ->setActive(true)
                        ->setDateStart($newLicense['licenseDateStart'])
                        ->setDateEnd($newLicense['licenseDateEnd'])
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
     * @param array{
     *       license: string,
     *       firstName: string,
     *       lastName: string,
     *       gender: ?Gender,
     *       phone: string,
     *       email: string,
     *       location: string,
     *       status: string,
     *       licenseDateStart: ?\DateTime,
     *       licenseDateEnd: ?\DateTime,
     *       licenseType: string,
     *       category: string
     *  } $archerData
     *
     * @throws \RuntimeException Si pas de numéro de licence fourni
     */
    private function getArcher(array &$archers, array $archerData): Archer
    {
        if ($archerData['license'] && isset($archers[$archerData['license']])) {
            return $archers[$archerData['license']];
        }

        if ($archerData['license']) {
            $archer = (new Archer())
                ->setFirstName($archerData['firstName'])
                ->setLastName($archerData['lastName'])
                ->setLicenseNumber($archerData['license'])
                ->setPhone($archerData['phone'])
                // Todo: Gérer les doublons
                // ->setEmail($archerData['email'])
                ->setGender($archerData['gender'])
            ;

            $this->em->persist($archer);

            $archers[$archer->getLicenseNumber()] = $archer;

            return $archer;
        }

        throw new \RuntimeException('Licence not found');
    }
}
