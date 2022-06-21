<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Model\Badge;
use App\Domain\Result\Model\ResultBadge;
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
    name: 'app:v2-to-v3:progress-arrow',
    description: 'Migration des résultats des fleches de progression de la version 2 du site vers la 3',
)]
class V2ToV3ProgressArrowCommand extends Command
{
    use ArcherTrait;

    public function __construct(private readonly EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $colors = [
            'blanche' => (new Badge())
                ->setName('Flèches Banche')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(1)
                ->setCode('arrow_white')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'noire' => (new Badge())
                ->setName('Flèches Noir')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(2)
                ->setCode('arrow_black')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'bleue' => (new Badge())
                ->setName('Flèches Bleue')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(3)
                ->setCode('arrow_blue')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'rouge' => (new Badge())
                ->setName('Flèches Rouge')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(4)
                ->setCode('arrow_red')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'jaune' => (new Badge())
                ->setName('Flèches Jaune')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(5)
                ->setCode('arrow_yellow')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'bronze' => (new Badge())
                ->setName('Flèches de Bronze')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(6)
                ->setCode('arrow_bronze')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'argent' => (new Badge())
                ->setName('Flèches d\'Argent')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(7)
                ->setCode('arrow_silver')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
            'or' => (new Badge())
                ->setName('Flèches d\'Or')
                ->setOfficial(true)
                ->setType('progress_arrow')
                ->setLevel(8)
                ->setCode('arrow_god')
                ->setConditions([
                    'type' => 'minScore',
                    'score' => 0,
                ]),
        ];

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery('SELECT * FROM adc_fleches', $rsm);

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('licence', 'licence');
        $rsm->addScalarResult('nom', 'nom');
        $rsm->addScalarResult('categorie', 'categorie');

        foreach ($colors as $color => $progressArrow) {
            $rsm->addScalarResult($color.'_date', $color.'_date', Types::DATE_IMMUTABLE);
            $rsm->addScalarResult($color.'_score', $color.'_score', Types::INTEGER);
            $rsm->addScalarResult($color.'_arme', $color.'_arme');

            $this->em->persist($progressArrow);
        }

        /** @var array<array> $progressArrowResults */
        $progressArrowResults = $nativeQuery->getArrayResult();

        $archers = $this->reformatArchersArray($this->em->getRepository(Archer::class)->findAll());

        foreach ($progressArrowResults as $progressArrowResult) {
            try {
                $archer = $this->getArcher($archers, $progressArrowResult['licence'], $progressArrowResult['nom']);
            } catch (Exception $e) {
                $io->error($e->getMessage());

                break;
            }

            foreach ($colors as $color => $progressArrow) {
                if ($progressArrowResult[$color.'_score']) {
                    $result = (new ResultBadge());
                    $result->setArcher($archer);
                    $result->setCategory(Category::createFromString($progressArrowResult['categorie']));
                    $result->setRecord(false);
                    $result->setScore($progressArrowResult[$color.'_score']);
                    $result->setBadge($progressArrow);
                    $result->setWeapon(Weapon::createFromString($progressArrowResult[$color.'_arme']));
                    $result->setCompletionDate($progressArrowResult[$color.'_date']);

                    if ($progressArrowResult[$color.'_date'] && ($date = DateTimeImmutable::createFromFormat('U', $progressArrowResult[$color.'_date']->format('U')))) {
                        $result->setCompletionDate($date);
                    }

                    $this->em->persist($result);
                }
            }
        }

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }
}
