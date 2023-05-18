<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230518180833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Feat / Intégration de Trapta';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trapta (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', event_name VARCHAR(255) NOT NULL, positions JSON DEFAULT NULL, rankings JSON DEFAULT NULL, matches JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_4EE48D141E832AD (event_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE trapta');
    }
}
