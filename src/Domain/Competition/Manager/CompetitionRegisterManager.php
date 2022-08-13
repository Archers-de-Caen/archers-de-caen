<?php

declare(strict_types=1);

namespace App\Domain\Competition\Manager;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Domain\Competition\Model\CompetitionRegister;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CompetitionRegisterManager
{
    public function __construct(readonly private Environment $environment)
    {
    }

    /**
     * Créer une actualité depuis un formulaire d'inscription au concours.
     * L'actualité est créée en brouillon, elle doit être persist et flush.
     */
    public function createActuality(CompetitionRegister $competitionRegister): Page
    {
        try {
            $html = $this->environment->render('landing/competition-registers/actuality.html.twig', [
                'competitionRegister' => $competitionRegister,
            ]);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            // TODO: faire quelque chose de $e

            $html = 'Une erreur est survenue';
        }

        return (new Page())
            ->setCategory(Category::ACTUALITY)
            ->setTitle('Inscription au concours des Archers de Caen')
            ->setContent($html)
        ;
    }
}
