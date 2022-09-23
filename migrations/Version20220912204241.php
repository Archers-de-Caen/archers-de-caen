<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220912204241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Augmentation du nombre de caractère pour les mails';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_request CHANGE content content LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
