<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240625192327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'fix(competition): change ffta_code to nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition CHANGE ffta_code ffta_code VARCHAR(191) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition CHANGE ffta_code ffta_code VARCHAR(191) NOT NULL');
    }
}
