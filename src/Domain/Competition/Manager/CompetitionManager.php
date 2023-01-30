<?php

declare(strict_types=1);

namespace App\Domain\Competition\Manager;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Domain\Competition\Model\Competition;
use App\Http\Landing\Controller\CompetitionController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CompetitionManager
{
    public function __construct(readonly private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * Créer une actualité depuis une compétition.
     * L'actualité est créée en brouillon, elle doit être persist et flush.
     */
    public function createActuality(Competition $competition): Page
    {
        $iframeUrl = $this->urlGenerator->generate(CompetitionController::ROUTE_LANDING_RESULTS_COMPETITION, [
            'slug' => $competition->getSlug(),
        ]).'?iframe=true';

        return (new Page())
            ->setCategory(Category::ACTUALITY)
            ->setTitle('Résultat du '.$competition->__toString())
            ->setContent('<iframe src="'.$iframeUrl.'" class="fit-height-content"></iframe>')
        ;
    }
}
