<?php

declare(strict_types=1);

namespace App\Domain\Cms\Fixture;

use App\Domain\Archer\Fixture\ArcherFixture;
use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Infrastructure\Fixture\AbstractFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PageFixture extends AbstractFixtures implements DependentFixtureInterface
{
    public const LOAD_DATA_MAX = 100;
    public const REFERENCE = 'ref_page';

    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(Page::class, self::LOAD_DATA_MAX, function (Page $page): void {
            /** @var Archer $archer */
            $archer = $this->getReference(ArcherFixture::REFERENCE.'_'.$this->faker->numberBetween(0, ArcherFixture::LOAD_DATA_MAX - 1));

            self::create($page, $archer);

            $this->setReference($this->generateReference(self::REFERENCE), $page);
        });

        $this->createOne(Page::class, function (Page $page): void {
            /** @var Archer $archer */
            $archer = $this->getReference(ArcherFixture::REFERENCE.'_'.$this->faker->numberBetween(0, ArcherFixture::LOAD_DATA_MAX - 1));

            self::createWithTrueContent($page, $archer);
        });

        $manager->flush();
    }

    public static function create(Page $page, Archer $createdBy): Page
    {
        $faker = Factory::create('fr_FR');

        $page->setTitle($faker->sentence());
        $page->setContent($faker->randomHtml(6, 10));
        $page->setCreatedBy($createdBy);

        /** @var Category $category */
        $category = $faker->randomElement([Category::PAGE, Category::ACTUALITY]);
        $page->setCategory($category);

        /** @var Status $status */
        $status = $faker->randomElement([Status::PUBLISH, Status::DELETE, Status::DRAFT]);
        $page->setStatus($status);

        return $page;
    }

    public function createWithTrueContent(Page $page, Archer $createdBy): Page
    {
        $page->setTitle('Using PHP 8.1 enumerations in Symfony');
        $page->setContent((string) file_get_contents(__DIR__.'/page-content.html.twig'));
        $page->setCategory(Category::ACTUALITY);
        $page->setStatus(Status::PUBLISH);
        $page->setCreatedBy($createdBy);

        return $page;
    }

    public function getDependencies(): array
    {
        return [
            ArcherFixture::class,
        ];
    }
}
