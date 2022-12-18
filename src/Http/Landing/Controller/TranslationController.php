<?php

namespace App\Http\Landing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class TranslationController extends AbstractController
{
    public const ROUTE_TRANSLATION = 'translation';

    #[Route("/translation", name: self::ROUTE_TRANSLATION)]
    public function index(Request $request): Response
    {
        $locale = $request->getLocale();
        $translations = [];

        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../../../../translations/'.$locale);

        foreach ($finder as $file) {
            $parsed = Yaml::parse($file->getContents());

            $translations[explode('.', $file->getFilename())[0]] = $parsed;
        }

        try {
            $json = json_encode($translations, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $json = '{}';
        }

        $html = $this->renderView('translation/translation.js.twig', [
            'json' => $json,
        ]);

        return new Response($html, 200, ['Content-Type' => 'text/javascript']);
    }
}
