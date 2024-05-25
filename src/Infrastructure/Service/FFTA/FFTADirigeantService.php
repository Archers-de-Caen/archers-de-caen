<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FFTADirigeantService
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
        if (!$this->fftaUsername || !$this->fftaPassword) {
            throw new \RuntimeException('FFTA credentials are missing');
        }

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
    public function getLicenses(int $saison): array
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

        return LicenseDTO::createListFromCsv($content);
    }
}
