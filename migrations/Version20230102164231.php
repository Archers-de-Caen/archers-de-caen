<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230102164231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du nombre d\'inscrit a un départ stocké en dur';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register_departure ADD number_of_registered INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
