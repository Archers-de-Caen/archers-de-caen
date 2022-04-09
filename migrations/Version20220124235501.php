<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220124235501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Use php bin/console d:s:u --force';
    }

    public function up(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Use php bin/console d:s:u --force');

        $this->addSql('ALTER TABLE page MODIFY COLUMN content LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        $this->addSql('INSERT INTO page (created_by_id, title, content, category, status, created_at, updated_at, slug) SELECT 1, post_title, post_content, post_type, \'publish\', post_date, post_modified, post_name FROM wp_posts WHERE post_type in (\'page\', \'post\') AND post_status = \'publish\';');
        $this->addSql('UPDATE page SET category = \'actuality\' WHERE category = \'post\'');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
