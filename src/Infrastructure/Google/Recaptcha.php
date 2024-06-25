<?php

declare(strict_types=1);

namespace App\Infrastructure\Google;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class Recaptcha
{
    private string $clientSecret;

    private string $url;

    public function __construct(
        ParameterBagInterface $parameterBag,
        private readonly LoggerInterface $logger,
    ) {
        /** @var ?string $secret */
        $secret = $parameterBag->get('recaptcha_secret');
        $this->clientSecret = $secret ?? '';

        /** @var ?string $url */
        $url = $parameterBag->get('recaptcha_url');
        $this->url = $url ?? '';
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
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error($transportException);

            return false;
        }

        try {
            /** @var array $response */
            $response = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException|HttpExceptionInterface|TransportExceptionInterface $e) {
            $this->logger->error($e);

            return false;
        }

        if (!$response['success']) {
            return false;
        }

        return $response['score'] >= .8;
    }
}
