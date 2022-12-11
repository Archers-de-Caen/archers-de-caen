<?php

namespace App\Infrastructure\Google;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Recaptcha
{
    private string $clientSecret;
    private string $url;

    public function __construct(
        ParameterBagInterface $parameterBag,
        private readonly LoggerInterface $logger,
    ) {
        if (!($secret = $parameterBag->get('recaptcha_secret')) || !is_string($secret)) {
            throw new \InvalidArgumentException('Variable d\'environment "recaptcha_secret" invalide');
        }

        $this->clientSecret = $secret;

        if (!($url = $parameterBag->get('recaptcha_url')) || !is_string($url)) {
            throw new \InvalidArgumentException('Variable d\'environment "recaptcha_url" invalide');
        }

        $this->url = $url;
    }

    public function checkRecaptcha(string $clientSideToken, ?string $clientIp = null): bool
    {
        $params = [
            'body' => [
                'secret' => $this->clientSecret,
                'response' => $clientSideToken,
                'remoteip' => $clientIp,
            ],
        ];

        try {
            $request = HttpClient::create()->request(Request::METHOD_POST, $this->url, $params);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);

            return false;
        }

        try {
            /** @var array $response */
            $response = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException|HttpExceptionInterface|TransportExceptionInterface $e) {
            $this->logger->error($e);

            return false;
        }

        if (!$response['success']) {
            return false;
        }

        if ($response['score'] < .8) {
            return false;
        }

        return true;
    }
}
