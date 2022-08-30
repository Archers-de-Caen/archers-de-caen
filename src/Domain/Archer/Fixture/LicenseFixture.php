<?php

declare(strict_types=1);

namespace App\Domain\Archer\Fixture;

use App\Domain\Archer\Config\LicenseType;
use App\Domain\Archer\Model\License;
use App\Infrastructure\Fixture\AbstractFixtures;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LicenseFixture extends AbstractFixtures
{
    public const LOAD_DATA_MAX = 15;
    public const REFERENCE = 'ref_license';

    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(License::class, self::LOAD_DATA_MAX, function (License $license): void {
            self::create($license);

            $this->setReference($this->generateReference(self::REFERENCE), $license);
        });

        $manager->flush();
    }

    public static function create(License $license): License
    {
        $faker = Factory::create('fr_FR');

        /** @var LicenseType $licenseType */
        $licenseType = $faker->randomElement([
            LicenseType::ADULT,
            LicenseType::YOUNG,
            LicenseType::PARASPORTS,
            LicenseType::OTHER,
        ]);

        $license->setTitle($faker->sentence());
        $license->setType($licenseType);
        $license->setDescription($faker->sentence());
        $license->setPrice($faker->numberBetween(10, 200));

        return $license;
    }
}
