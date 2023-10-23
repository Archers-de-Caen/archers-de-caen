<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231023211008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jours de la FFTA qui a ajouté un 0 devant les numéros de licence';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE archer SET license_number = CONCAT(\'0\', license_number)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
