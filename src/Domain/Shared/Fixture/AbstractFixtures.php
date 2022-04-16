<?php

declare(strict_types=1);

namespace App\Domain\Shared\Fixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class AbstractFixtures extends Fixture
{
    protected Generator $faker;
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create('fr_FR');

        $this->loadData($manager);
    }

    protected function createMany(string $className, int $count, callable $seeder): array
    {
        $entities = [];
        for ($i = 0; $i < $count; ++$i) {
            $entities[] = $this->createOne($className, $seeder);

            echo "      > $i create $className\n";
        }

        return $entities;
    }

    protected function createOne(string $className, callable $seeder): object
    {
        $entity = new $className();

        $seeder($entity);

        $this->manager->persist($entity);

        return $entity;
    }

    protected function generateReference(string $prefix): string
    {
        $i = 0;
        while ($this->hasReference($prefix.'_'.$i)) {
            ++$i;
        }

        return $prefix.'_'.$i;
    }

    abstract protected function loadData(ObjectManager $manager): void;
}
