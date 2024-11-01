<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231115221003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'feat: add external auth (oauth) fields to archer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE archer ADD external_auth_id VARCHAR(255) DEFAULT NULL, ADD external_auth_type VARCHAR(255) DEFAULT NULL, ADD hosted_domain VARCHAR(255) DEFAULT NULL');


        $this->addSql('ALTER TABLE archer CHANGE license_number license_number VARCHAR(8) DEFAULT NULL');
        $this->addSql('ALTER TABLE competition_register_departure_target_archer CHANGE license_number license_number VARCHAR(8) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
