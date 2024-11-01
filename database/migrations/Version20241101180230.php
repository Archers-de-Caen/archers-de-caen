<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241101180230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'feat(newspaper): add newspaper access table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE newspaper_access (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(191) NOT NULL, password VARCHAR(191) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE newspaper_access');
    }
}
