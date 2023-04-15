<?php

declare(strict_types=1);

namespace App\Http\Api\Controller;

use App\Domain\Archer\Manager\ArcherManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/competition-registers/archers/{licenseNumber}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class CompetitionRegisterController extends AbstractController
{
    public const ROUTE = 'api_competition_register';

    public function __invoke(string $licenseNumber, ArcherManager $archerManager): Response
    {
        $archer = $archerManager->findArcherFromLicense($licenseNumber);

        if (!$archer) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        return $this->json($archerManager->generateAnonymizeArray($archer));
    }
}
