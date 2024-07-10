<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221025200014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du choix de l\'arme utilisé leurs de l\'inscription à un concours de Caen';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register_departure_target_archer ADD weapon VARCHAR(191) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
