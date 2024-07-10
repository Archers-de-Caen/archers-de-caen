<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221030120013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Système de paiement des concours';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register_departure_target_archer ADD paid TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('CREATE TABLE webhook (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) NOT NULL, service VARCHAR(255) NOT NULL, reference VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', result VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
