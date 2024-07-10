<?php

namespace App\Tests\Ressources\Services\Fixtures;

use App\Helper\PathHelper;
use Fidry\AliceDataFixtures\LoaderInterface;

trait FixturesTrait
{
    /**
     * Charge une série de fixture en base de donnée et ajoute les entités à l'EntityManager.
     *
     * @param array<string> $fixtures
     *
     * @return array<string,object>
     */
    public function loadFixtures(array $fixtures): array
    {
        $fixturePath = $this->getFixturesPath();
        $files = array_map(static fn ($fixture) => PathHelper::join($fixturePath, $fixture.'.yaml'), $fixtures);

        /** @var LoaderInterface $loader */
        $loader = static::getContainer()->get('fidry_alice_data_fixtures.loader.doctrine');

        return $loader->load($files);
    }

    public function getFixturesPath(): string
    {
        return __DIR__.'/../../../../database/fixtures/';
    }
}
