<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231025193541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jours de la FFTA qui a ajouté un 0 devant les numéros de licence';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register_departure_target_archer CHANGE license_number license_number VARCHAR(8) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
