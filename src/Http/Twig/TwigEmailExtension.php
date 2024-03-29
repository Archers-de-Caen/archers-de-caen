<?php

declare(strict_types=1);

namespace App\Http\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class TwigEmailExtension extends AbstractExtension
{
    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown_email', [$this, 'markdownEmail'], [
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFilter('text_email', [$this, 'formatText']),
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
}
