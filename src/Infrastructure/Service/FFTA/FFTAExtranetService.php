<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FFTAExtranetService
{
    private const int STRUCTURE_ID = 657;

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
            'base_uri' => 'https://extranet.ffta.fr/',
        ]);
    }

    /**
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function connect(): void
    {
        if (!$this->fftaUsername || !$this->fftaPassword) {
            throw new \RuntimeException('FFTA credentials are missing');
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_POST,
            url: '/',
            options: [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'login' => [
                        'identifiant' => $this->fftaUsername,
                        'idpassword' => $this->fftaPassword,
                    ],
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
     * @return CompetitionResultDTO[]
     *
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function getCompetitionResults(int $season): array
    {
        $pageConnexion = new \DOMDocument();
        $pageConnexion->validateOnParse = true;

        $response = $this->httpClient->request(
            method: Request::METHOD_POST,
            url: 'extractions/eprv-resind.html',
            options: [
                'headers' => [
                    'Cookie' => $this->cookies,
                ],
                'body' => [
                    'search' => [
                        'Saison' => $season,
                        'Discipline' => 'all',
                        'TypeChampionnat' => 'all',
                        'Pers' => 'CLU',
                        'oldPers' => 'CLU',
                        'Struc' => self::STRUCTURE_ID,
                        'Date_dbt' => '01/01/'.($season - 1),
                        'Date_fin' => '31/12/'.($season + 1),
                    ],
                    'StartGen' => 'Générer les documents',
                ],
            ],
        );

        $pageConnexion->loadHTML($response->getContent(), \LIBXML_NOERROR);

        $nodes = $pageConnexion->getElementById('exports_container')?->getElementsByTagName('a');

        if (!$nodes) {
            throw new \RuntimeException('CSV download link not found');
        }

        $csvDownloadLink = null;

        foreach ($nodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $href = $node->getAttribute('href');

            if (str_starts_with($href, 'https://extranet.ffta.fr/tmp/resultats/ResultatsIndividuels')) {
                $csvDownloadLink = $href;

                break;
            }
        }

        if (!$csvDownloadLink) {
            throw new \RuntimeException('CSV download link not found');
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_GET,
            url: str_replace('https://extranet.ffta.fr/', '', $csvDownloadLink),
            options: [
                'headers' => [
                    'Cookie' => $this->cookies,
                ],
            ],
        );

        return CompetitionResultDTO::createListFromCsv($response->getContent());
    }
}
