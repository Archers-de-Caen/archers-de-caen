<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240701214105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'feat(landing): add jeu de ete competition results page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE result ADD score_sheet_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD metadata JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE result ADD CONSTRAINT FK_136AC1137C11D9DD FOREIGN KEY (score_sheet_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_136AC1137C11D9DD ON result (score_sheet_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE result DROP FOREIGN KEY FK_136AC1137C11D9DD');
        $this->addSql('DROP INDEX UNIQ_136AC1137C11D9DD ON result');
        $this->addSql('ALTER TABLE result DROP score_sheet_id, DROP metadata');
    }
}
