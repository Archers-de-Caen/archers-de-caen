<?php

namespace App\Command\V2ToV3;

use App\Command\ArcherTrait;
use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Model\ProgressArrow;
use App\Domain\Competition\Model\ResultProgressArrow;
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

    public function __construct(private EntityManagerInterface $em, string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $colors = [
            'blanche' => (new ProgressArrow())->setName('Banche'),
            'noire' => (new ProgressArrow())->setName('Noir'),
            'bleue' => (new ProgressArrow())->setName('Bleue'),
            'rouge' => (new ProgressArrow())->setName('Rouge'),
            'jaune' => (new ProgressArrow())->setName('Jaune'),
            'bronze' => (new ProgressArrow())->setName('Bronze'),
            'argent' => (new ProgressArrow())->setName('Argent'),
            'or' => (new ProgressArrow())->setName('Or'),
        ];

        $rsm = new ResultSetMapping();
        $nativeQuery = $this->em->createNativeQuery("SELECT * FROM adc_fleches", $rsm);

        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('licence', 'licence');
        $rsm->addScalarResult('nom', 'nom');
        $rsm->addScalarResult('categorie', 'categorie');

        foreach ($colors as $color => $progressArrow) {
            $rsm->addScalarResult($color . '_date', $color . '_date', Types::DATE_IMMUTABLE);
            $rsm->addScalarResult($color . '_score', $color . '_score', Types::INTEGER);
            $rsm->addScalarResult($color . '_arme', $color . '_arme');

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
                if ($progressArrowResult[$color . '_score']) {
                    $result = (new ResultProgressArrow());
                    $result->setArcher($archer);
                    $result->setCategory(Category::createFromString($progressArrowResult['categorie']));
                    $result->setRecord(false);
                    $result->setScore($progressArrowResult[$color . '_score']);
                    $result->setProgressArrow($progressArrow);
                    $result->setWeapon(Weapon::createFromString($progressArrowResult[$color . '_arme']));

                    $this->em->persist($result);
                }
            }
        }

        $this->em->flush();

        $io->success('Finish');

        return Command::SUCCESS;
    }
}
