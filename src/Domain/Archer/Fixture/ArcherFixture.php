<?php

declare(strict_types=1);

namespace App\Domain\Archer\Fixture;

use App\Domain\Archer\Model\Archer;
use App\Domain\Shared\Fixture\AbstractFixtures;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ArcherFixture extends AbstractFixtures
{
    public const LOAD_DATA_MAX = 15;
    public const REFERENCE = 'ref_archer';
    public const REFERENCE_ADMIN = 'ref_archer_admin';

    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(Archer::class, self::LOAD_DATA_MAX, function (Archer $archer) {
            self::create($archer);

            $this->setReference($this->generateReference(self::REFERENCE), $archer);
        });

        $this->createOne(Archer::class, function (Archer $archerAdmin) {
            self::createAdmin($archerAdmin);

            $this->setReference($this->generateReference(self::REFERENCE_ADMIN), $archerAdmin);
        });

        $manager->flush();
    }

    public static function create(Archer $archer): Archer
    {
        $faker = Factory::create('fr_FR');

        $archer->setFirstName($faker->firstName());
        $archer->setLastName($faker->lastName());
        $archer->setLicenseNumber($faker->numberBetween(100000, 999999) . $faker->randomLetter());
        $archer->setPlainPassword($faker->password());
        $archer->setEmail($faker->email());
        $archer->setPhone($faker->e164PhoneNumber());

        return $archer;
    }

    public static function createAdmin(Archer $archer): Archer
    {
        $archer = self::create($archer);

        $archer->setPlainPassword('Pwd123');
        $archer->addRole(Archer::ROLE_ADMIN);

        return $archer;
    }
}
