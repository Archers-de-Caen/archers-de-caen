<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240528191755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'feat(competition): Add ffta_code to competition, and add departure_number to result_competition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition ADD ffta_code VARCHAR(191) NOT NULL');
        $this->addSql('ALTER TABLE result_competition ADD departure_number INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
