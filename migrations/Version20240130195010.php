<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240130195010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'chore: update photo table for testing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photo CHANGE image_size image_size INT DEFAULT NULL, CHANGE image_dimension image_dimension LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE photo CHANGE image_mime_type image_mime_type VARCHAR(191) DEFAULT NULL, CHANGE image_original_name image_original_name VARCHAR(191) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
