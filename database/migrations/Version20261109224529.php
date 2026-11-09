<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109224529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "L'email de l'archer est maintenant non obligatoire";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE archer CHANGE email email VARCHAR(191) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE archer CHANGE email email VARCHAR(191) NOT NULL');
    }
}
