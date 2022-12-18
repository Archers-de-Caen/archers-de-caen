<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221218130950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'inscription par équipe aux concours';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register ADD by_team SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql("UPDATE competition_register_departure_target_archer SET category = REPLACE(REPLACE(category, '_woman', ''), '_man', '') WHERE 1");
        $this->addSql("UPDATE archer SET category = REPLACE(REPLACE(category, '_woman', ''), '_man', '') WHERE 1");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
