<?php

declare(strict_types=1);

namespace App\Http\Twig;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TwigEmailExtension extends AbstractExtension
{
    /**
     * @param string $baseHost auto-inject from services.yaml
     */
    public function __construct(
        readonly string $baseHost,
        private readonly LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown_email', [$this, 'markdownEmail'], [
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFilter('text_email', [$this, 'formatText']),
            new TwigFilter('replace_iframes', [$this, 'replaceIframesWithContent'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Convertit le contenu markdown en HTML.
     */
    public function markdownEmail(array $context, string $content): string
    {
        if (($context['format'] ?? 'text') === 'text') {
            return $content;
        }

        $content = preg_replace('/^(^ {2,})(\S+[ \S]*)$/m', '${2}', $content);

        return (new \Parsedown())->setSafeMode(false)->text($content);
    }

    public function formatText(string $content): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/^(^ {2,})(\S+[ \S]*)$/m', '${2}', $content) ?: '';

        return preg_replace("/([\r\n] *){3,}/", "\n\n", $content) ?: '';
    }

    public function replaceIframesWithContent(string $content): string
    {
        $urls = $this->extractIframeUrls($content);

        foreach ($urls as $url) {
            try {
                $iframeContent = $this->fetchIframeContent($url);
            } catch (\Throwable $th) {
                $this->logger->error('Failed to fetch iframe content', [
                    'url' => $url,
                    'exceptionMessage' => $th->getMessage(),
                ]);

                continue;
            }

            $content = preg_replace(
                pattern: sprintf('/<iframe[^>]+src="%s".*?<\/iframe>/i', preg_quote($url, '/')),
                replacement: "
                    <figure class='iframe-flatten'>
                        {$iframeContent}
                    </figure>
                ",
                subject: $content ?? ''
            );
        }

        if (!\is_string($content)) {
            return '';
        }

        return $content;
    }

    private function extractIframeUrls(string $content): array
    {
        preg_match_all('/<iframe[^>]+src="([^"]+)"/i', $content, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return [];
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function fetchIframeContent(string $url): string
    {
        if (str_starts_with($url, '/')) {
            $url = $this->baseHost.$url;
        }

        $client = HttpClient::create();
        $response = $client->request(Request::METHOD_GET, $url);

        return $response->getContent();
    }
}
