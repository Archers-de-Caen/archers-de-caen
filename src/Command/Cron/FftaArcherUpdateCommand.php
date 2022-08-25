<?php

declare(strict_types=1);

namespace App\Command\Cron;

use App\Command\ArcherTrait;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\ArcherLicense;
use App\Domain\Archer\Model\License;
use CurlHandle;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use DOMElement;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ffta:archer-update',
    description: 'Met à jour les archers licencié depuis le site de la FFTA',
)]
class FftaArcherUpdateCommand extends Command
{
    use ArcherTrait;

    private string $cookieFile;
    private CurlHandle $curl;

    /**
     * @param string $fftaUsername Injected from service.yaml
     * @param string $fftaPassword Injected from service.yaml
     */
    public function __construct(private EntityManagerInterface $em, private string $fftaUsername, private string $fftaPassword, string $name = null)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($cookieFile = tempnam(sys_get_temp_dir(), 'FFTA')) {
            $this->cookieFile = $cookieFile;
        } else {
            $io->error('Impossible de générer $cookieFile');

            return Command::FAILURE;
        }

        $this->curl = curl_init();

        $season = (int) date('m') < 9 ? date('Y') : (string) ((int) date('Y') + 1);

        // On se connecte à l'espace dirigeant
        $this->connect();

        $licensesResponse = $this->getLicenses($season);

        if ('success' != $licensesResponse['status']) {
            foreach ($licensesResponse['errors'] as $error) {
                $io->writeln($error);
            }

            foreach ($licensesResponse['messages'] as $messages) {
                $io->writeln($messages);
            }

            return Command::FAILURE;
        }

        $newLicenses = $this->reformatLicencesArray($licensesResponse['licences']);
        $archers = $this->reformatArchersArray($this->em->getRepository(Archer::class)->findAll());
        $licenses = $this->em->getRepository(License::class)->findAll();

        foreach ($newLicenses as $newLicense) {
            try {
                $archer = $this->getArcher($archers, $newLicense['license'], $newLicense['name']);
            } catch (Exception $e) {
                $io->error($e->getMessage());

                break;
            }

            if (!$archer->getArcherLicenseActive()) {
                $license = array_filter($licenses, fn (License $license) => '' === $license->getTitle());

                if (!count($license)) {
                    $io->error('Licence not found');
                }

                $archer->addArcherLicense(
                    (new ArcherLicense())
                        ->setActive(true)
                        ->setDateStart($newLicense['licenseDate'])
                        ->setDateEnd(DateTime::createFromFormat('Y-m-d', $season.'-08-31') ?: null)
                        ->setLicense($license[0])
                );
            }
        }

        curl_close($this->curl);

        unlink($this->cookieFile);

        $this->em->flush();

        return Command::SUCCESS;
    }

    /**
     * Récupère le token de connexion ainsi que les cookies de session.
     */
    private function getToken(): ?string
    {
        $pageConnexion = new DOMDocument();
        $pageConnexion->validateOnParse = true;

        curl_setopt($this->curl, CURLOPT_URL, 'https://ffta-goal.multimediabs.com/login');
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);

        $page = (string) curl_exec($this->curl);

        $pageConnexion->LoadHTML($page);

        $nodes = $pageConnexion->getElementsByTagName('input');

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            if ('authenticityToken' == $node->getAttribute('name')) {
                return $node->getAttribute('value');
            }
        }

        return null;
    }

    private function connect(): void
    {
        $postdata = [
            'username' => $this->fftaUsername,
            'password' => $this->fftaPassword,
            'authenticityToken' => $this->getToken(),
        ];

        curl_setopt($this->curl, CURLOPT_URL, 'https://ffta-goal.multimediabs.com/login');
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);

        curl_exec($this->curl);
    }

    private function getLicenses(string $saison): array
    {
        curl_setopt($this->curl, CURLOPT_URL, "https://ffta-goal.multimediabs.com/licences/afficherlistelicencies?editionAttestation=&idSaison=$saison");
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, [
            'X-Requested-With: XMLHttpRequest',
            'Host: ffta-goal.multimediabs.com',
            'Accept: application/json, text/javascript, */*; q=0.01',
            'Accept-Language: fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection: keep-alive',
        ]);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_REFERER, 'https://ffta-goal.multimediabs.com/licences/listerLicencies?idStructure=636');

        $json = curl_exec($this->curl);

        // Conversion des caractères UTF8
        return (array) json_decode((string) preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', fn ($match) => mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE'), (string) $json));
    }

    private function reformatLicencesArray(array $licenses): array
    {
        $formattedLicenses = [];

        foreach ($licenses as $license) {
            $formattedLicenses[] = [
                'license' => $license[0],
                'name' => $license[1],
                'gender' => $license[2],
                'location' => $license[3],
                'status' => $license[4],
                'birthDate' => DateTime::createFromFormat('d/m/Y', $license[5]),
                'licenseDate' => DateTime::createFromFormat('d/m/Y', $license[6]),
                'licenseType' => $license[7],
                'category' => $license[8],
                'html' => $license[9],
            ];
        }

        return $formattedLicenses;
    }
}
