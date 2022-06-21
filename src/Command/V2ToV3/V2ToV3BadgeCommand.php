<?php

declare(strict_types=1);

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Model\Badge;
use App\Domain\Competition\Config\Type;
use App\Domain\Result\Model\ResultBadge;
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
    name: 'app:v2-to-v3:badge',
    description: 'Migration des distinctions fédérales (badge) de la version 2 du site vers la 3',
)]
final class V2ToV3BadgeCommand extends Command
{
    use ArcherTrait;

    public function __construct(private readonly EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $badges = self::generateBadges();

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery('SELECT * FROM adc_distinctions', $rsm);

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('licence', 'license');
        $rsm->addScalarResult('nom', 'name');
        $rsm->addScalarResult('type', 'type');
        $rsm->addScalarResult('categorie', 'category');
        $rsm->addScalarResult('date', 'date', Types::DATE_IMMUTABLE);
        $rsm->addScalarResult('score', 'score');

        /** @var array<array> $recordResults */
        $recordResults = $nativeQuery->getArrayResult();

        $archers = $this->reformatArchersArray($this->em->getRepository(Archer::class)->findAll());

        foreach ($badges as $badgeType) {
            foreach ($badgeType as $badge) {
                $this->em->persist($badge);
            }
        }

        foreach ($recordResults as $recordResult) {
            try {
                $archer = $this->getArcher($archers, $recordResult['license'], $recordResult['name']);
            } catch (Exception $e) {
                $io->error($e->getMessage());

                break;
            }

            if (isset($badges[Type::createFromString($recordResult['type'])->value])) {
                foreach ($badges[Type::createFromString($recordResult['type'])->value] as $badge) {
                    if (isset($badge->getConditions()['score']) && $recordResult['score'] >= $badge->getConditions()['score']) {
                        $result = (new ResultBadge());
                        $result->setArcher($archer);
                        $result->setRecord(false);
                        $result->setScore($recordResult['score']);
                        $result->setWeapon(
                            Weapon::createFromString(substr($recordResult['category'], strlen($recordResult['category']) - 2, 2))
                        );
                        $result->setCategory(
                            Category::createFromString(substr($recordResult['category'], 0, -2))
                        );

                        $result->setCompletionDate($recordResult['date']);

                        $result->setBadge($badge);

                        $this->em->persist($result);
                    }
                }
            }
        }

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }

    /**
     * @return array<string, array<Badge>>
     */
    private static function generateBadges(): array
    {
        $baseBadge = (new Badge())->setOfficial(true)->setType('competition');

        $baseIndoorBadge = (clone $baseBadge)->setCompetitionType(Type::INDOOR_2x18_M);
        $baseFitaBadge = (clone $baseBadge)->setCompetitionType(Type::FITA);
        $baseFitaStarBadge = (clone $baseBadge)->setCompetitionType(Type::FITA_STAR);
        $baseFederal50Badge = (clone $baseBadge)->setCompetitionType(Type::FEDERAL_2x50_M);
        $baseBeursaultBadge = (clone $baseBadge)->setCompetitionType(Type::BEURSAULT);
        $baseCampagneBadge = (clone $baseBadge)->setCompetitionType(Type::CAMPAGNE);
        $baseThreeDBadge = (clone $baseBadge)->setCompetitionType(Type::THREE_D);
        $baseNatureBadge = (clone $baseBadge)->setCompetitionType(Type::NATURE);

        return [
            Type::INDOOR_2x18_M->value => [
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_green')
                    ->setName('Badge vert')
                    ->setConditions(['type' => 'minScore', 'score' => 455, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_white')
                    ->setName('Badge blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 480, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_black')
                    ->setName('Badge noir')
                    ->setConditions(['type' => 'minScore', 'score' => 500, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_blue')
                    ->setName('Badge bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 515, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_red')
                    ->setName('Badge rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 530, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_yellow')
                    ->setName('Badge jaune')
                    ->setConditions(['type' => 'minScore', 'score' => 545, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_1_star')
                    ->setName('Badge 1 étoile')
                    ->setConditions(['type' => 'minScore', 'score' => 550, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_2_stars')
                    ->setName('Badge 2 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 565, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::RECURVE_BOW->value.'_3_stars')
                    ->setName('Badge 3 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 575, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_green')
                    ->setName('Badge vert')
                    ->setConditions(['type' => 'minScore', 'score' => 455, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_white')
                    ->setName('Badge blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 480, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_black')
                    ->setName('Badge noir')
                    ->setConditions(['type' => 'minScore', 'score' => 500, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_blue')
                    ->setName('Badge bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 515, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_red')
                    ->setName('Badge rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 530, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_yellow')
                    ->setName('Badge jaune')
                    ->setConditions(['type' => 'minScore', 'score' => 545, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_1_star')
                    ->setName('Badge 1 étoile')
                    ->setConditions(['type' => 'minScore', 'score' => 550, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_2_stars')
                    ->setName('Badge 2 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 565, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::BARE_BOW->value.'_3_stars')
                    ->setName('Badge 3 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 575, 'weapon' => Weapon::BARE_BOW->value]),

                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_green')
                    ->setName('Badge vert')
                    ->setConditions(['type' => 'minScore', 'score' => 455, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_white')
                    ->setName('Badge blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 480, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_black')
                    ->setName('Badge noir')
                    ->setConditions(['type' => 'minScore', 'score' => 500, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_blue')
                    ->setName('Badge bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 515, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_red')
                    ->setName('Badge rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 530, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_yellow')
                    ->setName('Badge jaune')
                    ->setConditions(['type' => 'minScore', 'score' => 545, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_1_star')
                    ->setName('Badge 1 étoile')
                    ->setConditions(['type' => 'minScore', 'score' => 550, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_2_stars')
                    ->setName('Badge 2 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 565, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseIndoorBadge)
                    ->setCode(Type::INDOOR_2x18_M->value.'_'.Weapon::COMPOUND_BOW->value.'_3_stars')
                    ->setName('Badge 3 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 575, 'weapon' => Weapon::COMPOUND_BOW->value]),
            ],

            Type::FITA->value => [
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_green')
                    ->setName('Badge vert')
                    ->setConditions(['type' => 'minScore', 'score' => 480, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_white')
                    ->setName('Badge blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 510, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_black')
                    ->setName('Badge noir')
                    ->setConditions(['type' => 'minScore', 'score' => 535, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_blue')
                    ->setName('Badge bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 560, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_red')
                    ->setName('Badge rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 585, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_yellow')
                    ->setName('Badge jaune')
                    ->setConditions(['type' => 'minScore', 'score' => 605, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_1_star')
                    ->setName('Badge 1 étoile')
                    ->setConditions(['type' => 'minScore', 'score' => 625, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_2_stars')
                    ->setName('Badge 2 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 645, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::RECURVE_BOW->value.'_3_stars')
                    ->setName('Badge 3 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 660, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_green')
                    ->setName('Badge vert')
                    ->setConditions(['type' => 'minScore', 'score' => 480, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_white')
                    ->setName('Badge blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 510, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_black')
                    ->setName('Badge noir')
                    ->setConditions(['type' => 'minScore', 'score' => 535, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_blue')
                    ->setName('Badge bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 560, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_red')
                    ->setName('Badge rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 585, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_yellow')
                    ->setName('Badge jaune')
                    ->setConditions(['type' => 'minScore', 'score' => 605, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_1_star')
                    ->setName('Badge 1 étoile')
                    ->setConditions(['type' => 'minScore', 'score' => 625, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_2_stars')
                    ->setName('Badge 2 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 645, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::BARE_BOW->value.'_3_stars')
                    ->setName('Badge 3 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 660, 'weapon' => Weapon::BARE_BOW->value]),

                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_green')
                    ->setName('Badge vert')
                    ->setConditions(['type' => 'minScore', 'score' => 620, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_white')
                    ->setName('Badge blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 635, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_black')
                    ->setName('Badge noir')
                    ->setConditions(['type' => 'minScore', 'score' => 645, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_blue')
                    ->setName('Badge bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 655, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_red')
                    ->setName('Badge rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 665, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_yellow')
                    ->setName('Badge jaune')
                    ->setConditions(['type' => 'minScore', 'score' => 675, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_1_star')
                    ->setName('Badge 1 étoile')
                    ->setConditions(['type' => 'minScore', 'score' => 685, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_2_stars')
                    ->setName('Badge 2 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 695, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFitaBadge)
                    ->setCode(Type::FITA->value.'_'.Weapon::COMPOUND_BOW->value.'_3_stars')
                    ->setName('Badge 3 étoiles')
                    ->setConditions(['type' => 'minScore', 'score' => 700, 'weapon' => Weapon::COMPOUND_BOW->value]),
            ],

            Type::FITA_STAR->value => [
                (clone $baseFitaStarBadge)
                    ->setCode(Type::FITA_STAR->value.'_'.Weapon::RECURVE_BOW->value)
                    ->setName('FITA STAR')
                    ->setConditions(['type' => 'minScore', 'score' => 950, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseFitaStarBadge)
                    ->setCode(Type::FITA_STAR->value.'_'.Weapon::BARE_BOW->value)
                    ->setName('FITA STAR')
                    ->setConditions(['type' => 'minScore', 'score' => 950, 'weapon' => Weapon::BARE_BOW->value]),

                (clone $baseFitaStarBadge)
                    ->setCode(Type::FITA_STAR->value.'_'.Weapon::COMPOUND_BOW->value)
                    ->setName('FITA STAR')
                    ->setConditions(['type' => 'minScore', 'score' => 950, 'weapon' => Weapon::COMPOUND_BOW->value]),
            ],

            Type::FEDERAL_2x50_M->value => [
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::RECURVE_BOW->value.'_1_archer')
                    ->setName('1 archer')
                    ->setConditions(['type' => 'minScore', 'score' => 500, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::RECURVE_BOW->value.'_2_archers')
                    ->setName('2 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 550, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::RECURVE_BOW->value.'_3_archers')
                    ->setName('3 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 600, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::RECURVE_BOW->value.'_4_archers')
                    ->setName('4 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 640, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::RECURVE_BOW->value.'_5_archers')
                    ->setName('5 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 670, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::BARE_BOW->value.'_1_archer')
                    ->setName('1 archer')
                    ->setConditions(['type' => 'minScore', 'score' => 500, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::BARE_BOW->value.'_2_archers')
                    ->setName('2 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 550, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::BARE_BOW->value.'_3_archers')
                    ->setName('3 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 600, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::BARE_BOW->value.'_4_archers')
                    ->setName('4 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 640, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::BARE_BOW->value.'_5_archers')
                    ->setName('5 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 670, 'weapon' => Weapon::BARE_BOW->value]),

                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::COMPOUND_BOW->value.'_1_archer')
                    ->setName('1 archer')
                    ->setConditions(['type' => 'minScore', 'score' => 500, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::COMPOUND_BOW->value.'_2_archers')
                    ->setName('2 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 550, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::COMPOUND_BOW->value.'_3_archers')
                    ->setName('3 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 600, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::COMPOUND_BOW->value.'_4_archers')
                    ->setName('4 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 640, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseFederal50Badge)
                    ->setCode(Type::FEDERAL_2x50_M->value.'_'.Weapon::COMPOUND_BOW->value.'_5_archers')
                    ->setName('5 archers')
                    ->setConditions(['type' => 'minScore', 'score' => 670, 'weapon' => Weapon::COMPOUND_BOW->value]),
            ],

            Type::BEURSAULT->value => [
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::RECURVE_BOW->value.'_1_marmot')
                    ->setName('1 marmot')
                    ->setConditions(['type' => 'minScore', 'score' => 32, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::RECURVE_BOW->value.'_2_marmots')
                    ->setName('2 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 35, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::RECURVE_BOW->value.'_3_marmots')
                    ->setName('3 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 38, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::RECURVE_BOW->value.'_4_marmots')
                    ->setName('4 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 40, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::BARE_BOW->value.'_1_marmot')
                    ->setName('1 marmot')
                    ->setConditions(['type' => 'minScore', 'score' => 32, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::BARE_BOW->value.'_2_marmots')
                    ->setName('2 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 35, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::BARE_BOW->value.'_3_marmots')
                    ->setName('3 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 38, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::BARE_BOW->value.'_4_marmots')
                    ->setName('4 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 40, 'weapon' => Weapon::BARE_BOW->value]),

                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::COMPOUND_BOW->value.'_1_marmot')
                    ->setName('1 marmot')
                    ->setConditions(['type' => 'minScore', 'score' => 32, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::COMPOUND_BOW->value.'_2_marmots')
                    ->setName('2 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 35, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::COMPOUND_BOW->value.'_3_marmots')
                    ->setName('3 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 38, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseBeursaultBadge)
                    ->setCode(Type::BEURSAULT->value.'_'.Weapon::COMPOUND_BOW->value.'_4_marmots')
                    ->setName('4 marmots')
                    ->setConditions(['type' => 'minScore', 'score' => 40, 'weapon' => Weapon::COMPOUND_BOW->value]),
            ],

            Type::CAMPAGNE->value => [
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::RECURVE_BOW->value.'_wild_boar')
                    ->setName('Marcassin vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 150, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::RECURVE_BOW->value.'_squirrel_green_white')
                    ->setName('écureuil vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 190, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::RECURVE_BOW->value.'_squirrel_silver_green')
                    ->setName('écureuil argent sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 220, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::RECURVE_BOW->value.'_squirrel_gold_green')
                    ->setName('écureuil or sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 240, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::RECURVE_BOW->value.'_squirrel_gold_black')
                    ->setName('écureuil or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 270, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::COMPOUND_BOW->value.'_wild_boar')
                    ->setName('Marcassin vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 150, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::COMPOUND_BOW->value.'_squirrel_green_white')
                    ->setName('écureuil vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 200, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::COMPOUND_BOW->value.'_squirrel_silver_green')
                    ->setName('écureuil argent sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 230, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::COMPOUND_BOW->value.'_squirrel_gold_green')
                    ->setName('écureuil or sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 250, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::COMPOUND_BOW->value.'_squirrel_gold_black')
                    ->setName('écureuil or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 290, 'weapon' => Weapon::COMPOUND_BOW->value]),

                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::BARE_BOW->value.'_wild_boar')
                    ->setName('Marcassin vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 150, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::BARE_BOW->value.'_squirrel_green_white')
                    ->setName('écureuil vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 190, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::BARE_BOW->value.'_squirrel_silver_green')
                    ->setName('écureuil argent sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 220, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::BARE_BOW->value.'_squirrel_gold_green')
                    ->setName('écureuil or sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 240, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseCampagneBadge)
                    ->setCode(Type::CAMPAGNE->value.'_'.Weapon::BARE_BOW->value.'_squirrel_gold_black')
                    ->setName('écureuil or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 270, 'weapon' => Weapon::BARE_BOW->value]),
            ],

            Type::THREE_D->value => [
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::RECURVE_BOW->value.'_buck_green_white')
                    ->setName('brocard vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 310, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::RECURVE_BOW->value.'_buck_white_green')
                    ->setName('brocard blanc sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 430, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::RECURVE_BOW->value.'_buck_gold_white')
                    ->setName('brocard or sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 545, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::RECURVE_BOW->value.'_buck_gold_black')
                    ->setName('brocard or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 630, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::COMPOUND_BOW->value.'_buck_green_white')
                    ->setName('brocard vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 310, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::COMPOUND_BOW->value.'_buck_white_green')
                    ->setName('brocard blanc sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 430, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::COMPOUND_BOW->value.'_buck_gold_white')
                    ->setName('brocard or sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 545, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::COMPOUND_BOW->value.'_buck_gold_black')
                    ->setName('brocard or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 630, 'weapon' => Weapon::COMPOUND_BOW->value]),

                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::BARE_BOW->value.'_buck_green_white')
                    ->setName('brocard vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 310, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::BARE_BOW->value.'_buck_white_green')
                    ->setName('brocard blanc sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 430, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::BARE_BOW->value.'_buck_gold_white')
                    ->setName('brocard or sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 545, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseThreeDBadge)
                    ->setCode(Type::THREE_D->value.'_'.Weapon::BARE_BOW->value.'_buck_gold_black')
                    ->setName('brocard or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 630, 'weapon' => Weapon::BARE_BOW->value]),
            ],

            Type::NATURE->value => [
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::RECURVE_BOW->value.'_board_green_white')
                    ->setName('sanglier vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 60, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::RECURVE_BOW->value.'_board_gold_green')
                    ->setName('sanglier or sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 825, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::RECURVE_BOW->value.'_board_gold_white')
                    ->setName('sanglier or sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 1050, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::RECURVE_BOW->value.'_board_gold_black')
                    ->setName('sanglier or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 1200, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::RECURVE_BOW->value.'_board_gold_blue')
                    ->setName('sanglier or sur fond bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 1350, 'weapon' => Weapon::RECURVE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::RECURVE_BOW->value.'_board_gold_red')
                    ->setName('sanglier or sur fond rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 1425, 'weapon' => Weapon::RECURVE_BOW->value]),

                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::COMPOUND_BOW->value.'_board_green_white')
                    ->setName('sanglier vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 60, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::COMPOUND_BOW->value.'_board_gold_green')
                    ->setName('sanglier or sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 825, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::COMPOUND_BOW->value.'_board_gold_white')
                    ->setName('sanglier or sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 1050, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::COMPOUND_BOW->value.'_board_gold_black')
                    ->setName('sanglier or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 1200, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::COMPOUND_BOW->value.'_board_gold_blue')
                    ->setName('sanglier or sur fond bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 1350, 'weapon' => Weapon::COMPOUND_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::COMPOUND_BOW->value.'_board_gold_red')
                    ->setName('sanglier or sur fond rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 1425, 'weapon' => Weapon::COMPOUND_BOW->value]),

                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::BARE_BOW->value.'_board_green_white')
                    ->setName('sanglier vert sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 60, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::BARE_BOW->value.'_board_gold_green')
                    ->setName('sanglier or sur fond vert')
                    ->setConditions(['type' => 'minScore', 'score' => 825, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::BARE_BOW->value.'_board_gold_white')
                    ->setName('sanglier or sur fond blanc')
                    ->setConditions(['type' => 'minScore', 'score' => 1050, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::BARE_BOW->value.'_board_gold_black')
                    ->setName('sanglier or sur fond noir')
                    ->setConditions(['type' => 'minScore', 'score' => 1200, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::BARE_BOW->value.'_board_gold_blue')
                    ->setName('sanglier or sur fond bleu')
                    ->setConditions(['type' => 'minScore', 'score' => 1350, 'weapon' => Weapon::BARE_BOW->value]),
                (clone $baseNatureBadge)
                    ->setCode(Type::NATURE->value.'_'.Weapon::BARE_BOW->value.'_board_gold_red')
                    ->setName('sanglier or sur fond rouge')
                    ->setConditions(['type' => 'minScore', 'score' => 1425, 'weapon' => Weapon::BARE_BOW->value]),
            ],
        ];
    }
}
