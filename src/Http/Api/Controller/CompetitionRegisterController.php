<?php

declare(strict_types=1);

namespace App\Http\Api\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Competition\Repository\CompetitionRegisterRepository;
use App\Infrastructure\Service\Anonymize;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CompetitionRegisterController extends AbstractController
{
    public const ROUTE_API_COMPETITION_REGISTER = 'api_competition_register';

    #[Route('/competition-registers/archers/{licenseNumber}', name: self::ROUTE_API_COMPETITION_REGISTER)]
    public function resultsArrow(
        string $licenseNumber,
        CompetitionRegisterRepository $competitionRegisterRepository,
        ArcherRepository $archerRepository
    ): Response {
        if ($archer = $competitionRegisterRepository->findOneArcherByLicenseNumber($licenseNumber)) {
            return $this->json([
                'licenseNumber',
                'firstName',
                'lastName',
                'email',
                'phone',
                'gender',
                'category',
                'club',
            ]);
        }

        /** @var Archer $archer */
        if ($archer = $archerRepository->findOneBy(['licenseNumber' => $licenseNumber])) {
            return $this->json([
                'licenseNumber' => $archer->getLicenseNumber(),
                'firstName' => $archer->getFirstName(),
                'lastName' => $archer->getLastName(),
                'email' => $archer->getEmail() ? Anonymize::email($archer->getEmail()) : null,
                'phone' => $archer->getPhone() ? Anonymize::phone($archer->getPhone()) : null,
                'gender' => $archer->getGender(),
                'category' => $archer->getCategory(),
                'club' => 'Archers de Caen',
            ]);
        }

        return $this->json([], Response::HTTP_NOT_FOUND);
    }
}
