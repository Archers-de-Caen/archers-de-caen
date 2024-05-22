<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Config\Gender;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FFTAService
{
    private array $cookies = [];

    /**
     * @param string $fftaUsername Injected from service.yaml
     * @param string $fftaPassword Injected from service.yaml
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly string $fftaUsername,
        private readonly string $fftaPassword,
    ) {
        $this->httpClient = $this->httpClient->withOptions([
            'base_uri' => 'https://dirigeant.ffta.fr/',
        ]);
    }

    /**
     * Récupère le token de connexion ainsi que les cookies de session.
     *
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getToken(string $endpoint): ?string
    {
        $pageConnexion = new \DOMDocument();
        $pageConnexion->validateOnParse = true;

        $response = $this->httpClient->request(
            method: Request::METHOD_GET,
            url: $endpoint,
            options: [
                'headers' => [
                    'Cookie' => $this->cookies,
                ],
            ]
        );

        $this->setCookiesFromResponse($response);

        $pageConnexion->loadHTML($response->getContent(), \LIBXML_NOERROR);

        $nodes = $pageConnexion->getElementsByTagName('meta');

        /** @var \DOMElement $node */
        foreach ($nodes as $node) {
            if ('csrf-token' === $node->getAttribute('name')) {
                return $node->getAttribute('content');
            }
        }

        return null;
    }

    /**
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function connect(): void
    {
        $token = $this->getToken('auth/login');

        if (!$token) {
            throw new \RuntimeException('Token not found');
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_POST,
            url: 'auth/login',
            options: [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                    'Cookie' => $this->cookies,
                ],
                'body' => [
                    'username' => $this->fftaUsername,
                    'password' => $this->fftaPassword,
                    '_token' => $token,
                ],
            ]
        );

        $this->setCookiesFromResponse($response);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws HttpExceptionInterface
     */
    private function setCookiesFromResponse(ResponseInterface $response): void
    {
        $this->cookies = $response->getHeaders()['set-cookie'];
    }

    /**
     * @return array<LicenseDTO>
     *
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getLicenses(string $saison): array
    {
        $token = $this->getToken('extractions/licences');

        if (!$token) {
            throw new \RuntimeException('Token not found');
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_POST,
            url: 'extractions/licences/csv',
            options: [
                'body' => [
                    'etat' => '*A',
                    'saison' => $saison,
                    'saison_premiere_licence' => '0',
                    'prise_en_compte_compta' => '1',
                    'date_debut' => '',
                    'date_fin' => '',
                    'ia' => 'T',
                    'civilite' => 'T',
                    '_token' => $token,
                ],
                'headers' => [
                    'Cookie' => $this->cookies,
                ],
            ]
        );

        $contentType = $response->getHeaders()['content-type'][0] ?? null;

        if (!$contentType || !str_starts_with($contentType, 'text/csv')) {
            throw new \RuntimeException('Content type not found');
        }

        $content = $response->getContent();

        return $this->reformatLicencesArray($content);
    }

    /**
     * @return array<LicenseDTO>
     */
    private function reformatLicencesArray(string $licenses): array
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
            $license = explode(';', $row);

            if (empty($license[0])) {
                continue;
            }

            $formattedLicenses[] = new LicenseDTO(
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

        return $formattedLicenses;
    }

    public function getCompetitionResult(): array
    {
    }
}
