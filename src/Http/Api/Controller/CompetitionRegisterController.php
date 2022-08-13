<?php

declare(strict_types=1);

namespace App\Http\Api\Controller;

use App\Domain\Archer\Manager\ArcherManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CompetitionRegisterController extends AbstractController
{
    public const ROUTE_API_COMPETITION_REGISTER = 'api_competition_register';

    #[Route('/competition-registers/archers/{licenseNumber}', name: self::ROUTE_API_COMPETITION_REGISTER)]
    public function resultsArrow(string $licenseNumber, ArcherManager $archerManager): Response
    {
        $archer = $archerManager->findArcherFromLicense($licenseNumber);

        if (!$archer) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        return $this->json($archerManager->generateAnonymizeArray($archer));
    }
}
