<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Config\Weapon;
use App\Domain\Competition\Config\Type;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

use function Symfony\Component\Translation\t;

#[AsController]
#[Route(
    path: '/resultats/record',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class RecordController extends AbstractController
{
    public const ROUTE = 'landing_results_record';

    public function __invoke(ResultCompetitionRepository $resultCompetitionRepository): Response
    {
        /** @var ResultCompetition[] $resultRecords */
        $resultRecords = $resultCompetitionRepository
            ->createQueryBuilder('rc')

            ->select('rc')
            ->addSelect('c')
            ->addSelect('a')

            ->leftJoin('rc.competition', 'c')
            ->leftJoin('rc.archer', 'a')

            ->getQuery()
            ->getResult();

        $resultRecordsOrdered = [];

        foreach ($resultRecords as $resultRecord) {
            $competition = $resultRecord->getCompetition();

            if (!$competition) {
                continue;
            }

            $type = $competition->getType()?->value;
            $weapon = $resultRecord->getWeapon()?->value;
            $archer = $resultRecord->getArcher()?->getId()?->__toString();

            if (!$type || !$weapon || !$archer) {
                continue;
            }

            if (!isset($resultRecordsOrdered[$type])) {
                $resultRecordsOrdered[$type] = [];
            }

            if (!isset($resultRecordsOrdered[$type][$weapon])) {
                $resultRecordsOrdered[$type][$weapon] = [];
            }

            if (
                !isset($resultRecordsOrdered[$type][$weapon][$archer]) ||
                $resultRecordsOrdered[$type][$weapon][$archer]->getScore() < $resultRecord->getScore()
            ) {
                $resultRecordsOrdered[$type][$weapon][$archer] = $resultRecord;
            }
        }

        return $this->render('/landing/results/result-record.html.twig', [
            'resultRecords' => $resultRecordsOrdered,
            'weapons' => Weapon::getInOrder(),
            'competitionTypes' => array_filter(
                Type::getInOrder(),
                static function (Type $competitionType) use ($resultRecordsOrdered) {
                    foreach ($resultRecordsOrdered as $key => $resultRecordOrdered) {
                        if (!empty($resultRecordOrdered) && $key === t($competitionType->value, domain: 'competition')->getMessage()) {
                            return true;
                        }
                    }

                    return false;
                }
            ),
        ]);
    }
}
