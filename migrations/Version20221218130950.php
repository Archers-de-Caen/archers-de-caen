<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221218130950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajouter plusieurs archers en une inscription de concours";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register ADD by_team SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql("UPDATE competition_register_departure_target_archer SET category = REPLACE(REPLACE(category, '_woman', ''), '_man', '') WHERE 1");
        $this->addSql("UPDATE archer SET category = REPLACE(REPLACE(category, '_woman', ''), '_man', '') WHERE 1");

        $this->addSql('ALTER TABLE result ADD gender VARCHAR(191) DEFAULT NULL;');
        $this->addSql("UPDATE result SET gender = 'man' WHERE category LIKE '%_man'");
        $this->addSql("UPDATE result SET gender = 'woman' WHERE category LIKE '%_woman'");
        $this->addSql("UPDATE result SET category = REPLACE(REPLACE(category, '_woman', ''), '_man', '') WHERE 1");

        // Ajout du nombre d\'inscrit à un départ stocké en dur
        $this->addSql('ALTER TABLE competition_register_departure ADD number_of_registered INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
