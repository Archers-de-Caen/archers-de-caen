<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221119160123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout catégorie de l\'archer à la license';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE archer_license ADD category VARCHAR(191) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
