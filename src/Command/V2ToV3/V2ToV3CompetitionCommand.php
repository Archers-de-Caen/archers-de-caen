<?php

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Model\ResultCompetition;
use DateTime;
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

    public function __construct(private EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery("SELECT * FROM adc_results", $rsm);

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('lieu', 'lieu');
        $rsm->addScalarResult('dateDebut', 'dateDebut', Types::DATE_IMMUTABLE);
        $rsm->addScalarResult('dateFin', 'dateFin', Types::DATE_IMMUTABLE);
        $rsm->addScalarResult('noRecords', 'noRecords', Types::INTEGER);
        $rsm->addScalarResult('csv', 'csv');

        /** @var array<array{'id': string, 'csv': string, 'type': string, 'lieu': string, 'dateDebut': \DateTimeImmutable, 'dateFin': \DateTimeImmutable, 'noRecords': int}> $competitions */
        $competitions = $nativeQuery->getArrayResult();

        $archers = $this->reformatArchersArray($this->em->getRepository(Archer::class)->findAll());

        foreach ($competitions as $competition) {
            $newCompetition = (new Competition())
                ->setDateStart($competition['dateDebut'])
                ->setDateEnd($competition['dateFin'])
                ->setLocation($competition['lieu'])
                ->setType($competition['type']);

            foreach (explode('|', $competition['csv']) as $csv) {
                $result = $this->convertCsvColumnInArray($csv);

                try {
                    $archer = $this->getArcher($archers, $result['licence'], $result['name']);
                } catch (Exception $e) {
                    $io->error($e->getMessage());

                    break;
                }

                /** @var ResultCompetition $result */
                $result = (new ResultCompetition())
                    ->setArcher($archer)
                    ->setCategory(Category::createFromString($result['category']))
                    ->setRank($result['rank'])
                    ->setRecord(false)
                    ->setScore($result['score'])
                    ->setWeapon(Weapon::createFromString($result['weapon']))
                ;

                $newCompetition->addResult($result);

                $this->em->persist($newCompetition);
            }
        }

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }

    /**
     * @param string $csv
     *
     * @return array{
     *      'name': string, 'category': string, 'weapon': string, 'shot': int, 'score': int, 'date': Datetime|null,
     *      'rank': int, 'duel': string, 'html': string, 'licence': string|null
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
