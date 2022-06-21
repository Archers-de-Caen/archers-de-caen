<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Model\Competition;
use App\Domain\Result\Model\ResultCompetition;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:v2-to-v3:competition',
    description: 'Migration des résultats des compétitions de la version 2 du site vers la 3',
)]
class V2ToV3CompetitionCommand extends Command
{
    use ArcherTrait;

    public function __construct(private readonly EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery('SELECT * FROM adc_results', $rsm);

        $rsm->addScalarResult('id', 'id', Types::INTEGER);
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('lieu', 'location');
        $rsm->addScalarResult('dateDebut', 'dateStart', Types::DATE_IMMUTABLE);
        $rsm->addScalarResult('dateFin', 'dateEnd', Types::DATE_IMMUTABLE);
        $rsm->addScalarResult('noRecords', 'noRecords', Types::INTEGER);
        $rsm->addScalarResult('csv', 'csv');

        /** @var array<array{
         *     'id': int,
         *     'csv': string,
         *     'type': string,
         *     'location': string,
         *     'dateStart': DateTimeImmutable,
         *     'dateEnd': DateTimeImmutable,
         *     'noRecords': int
         * }> $competitions
         */
        $competitions = $nativeQuery->getArrayResult();

        $archers = $this->reformatArchersArray($this->em->getRepository(Archer::class)->findAll());

        foreach ($competitions as $competition) {
            $newCompetition = (new Competition());
            $newCompetition->setOldId($competition['id']);
            $newCompetition->setDateStart($competition['dateStart']);
            $newCompetition->setDateEnd($competition['dateEnd']);
            $newCompetition->setLocation($competition['location']);

            if (str_contains($competition['type'], 'Challenge de la Pomme d\'Or')) {
                $newCompetition->setType(Type::GOLDEN_APPLE_CHALLENGE);
            } elseif ('4 x 70m' === $competition['type']) {
                $newCompetition->setType(Type::FITA_4x70_M);
            } elseif (str_contains(strtolower($competition['type']), 'jeune')) {
                $newCompetition->setType(Type::SPECIAL_YOUNG);
            } elseif ('Salle 2x25m + 2x18m' === $competition['type']) {
                $newCompetition->setType(Type::INDOOR_2x18_M_2x25_M);
            } else {
                $newCompetition->setType(Type::createFromString($competition['type']));
            }

            $newCompetition->setSlug(sprintf(
                'Concours de %s %s du %s au %s',
                $competition['location'],
                $competition['type'],
                $competition['dateStart']->format('d-m-Y'),
                $competition['dateEnd']->format('d-m-Y')
            ));

            foreach (explode('|', $competition['csv']) as $csv) {
                $resultData = $this->convertCsvColumnInArray($csv);

                try {
                    $archer = $this->getArcher($archers, $resultData['licence'], $resultData['name']);
                } catch (Exception $e) {
                    $io->error($e->getMessage());

                    break;
                }

                /** @var ResultCompetition $result */
                $result = (new ResultCompetition())
                    ->setArcher($archer)
                    ->setCategory(Category::createFromString($resultData['category']))
                    ->setRank($resultData['rank'])
                    ->setRecord(false)
                    ->setScore($resultData['score'])
                    ->setWeapon(Weapon::createFromString($resultData['weapon']));

                if ($resultData['date'] && $date = DateTimeImmutable::createFromFormat('U', $resultData['date']->format('U'))) {
                    $result->setCompletionDate($date);
                }

                $newCompetition->addResult($result);
            }

            $this->em->persist($newCompetition);
        }

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }

    /**
     * @return array{
     *     'name': string,
     *     'category': string,
     *     'weapon': string,
     *     'shot': int,
     *     'score': int,
     *     'date': Datetime|null,
     *     'rank': int,
     *     'duel': string,
     *     'html': string,
     *     'licence': string|null
     * }
     */
    private function convertCsvColumnInArray(string $csv): array
    {
        $array = explode(';', $csv);

        return [
            'name' => $array[0],
            'category' => $array[1],
            'weapon' => $array[2],
            'shot' => (int) $array[3],
            'score' => (int) $array[4],
            'date' => $array[5] && ($date = DateTime::createFromFormat('d/m/Y', $array[5])) ? $date : null,
            'rank' => (int) $array[6],
            'duel' => $array[7],
            'html' => $array[8],
            'licence' => $array[9] ?? null,
        ];

//        $c['Html'] = str_replace('%3B', ';', $c['Html']);
//        $c['Html'] = str_replace('%7C', '|', $c['Html']);
    }
}
