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
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function checkRecaptcha(string $clientSideToken, ?string $clientIp = null): bool
    {
        $params = [
            'body' => [
                'secret' => $this->parameterBag->get('recaptcha_secret'),
                'response' => $clientSideToken,
                'remoteip' => $clientIp,
            ],
        ];

        try {
            $request = HttpClient::create()->request(Request::METHOD_POST, 'https://www.google.com/recaptcha/api/siteverify', $params);
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
