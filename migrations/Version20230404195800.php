<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230404195800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Feat Newsletter';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE newsletter (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', emails LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', context LONGTEXT NOT NULL, type VARCHAR(191) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("ALTER TABLE newsletter CHANGE context context LONGTEXT NOT NULL COMMENT '(DC2Type:json)'");
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE archer ADD newsletters LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');

        $this->addSql('ALTER TABLE gallery ADD status VARCHAR(191) DEFAULT \'draft\' NOT NULL');
        $this->addSql('ALTER TABLE page CHANGE status status VARCHAR(191) DEFAULT \'draft\' NOT NULL');

        $this->addSql("UPDATE gallery SET status = 'publish' WHERE TRUE");


        $this->addSql('ALTER TABLE archer CHANGE first_name first_name VARCHAR(191) DEFAULT NULL, CHANGE last_name last_name VARCHAR(191) DEFAULT NULL');
        $this->addSql('ALTER TABLE competition_register_departure_target_archer CHANGE first_name first_name VARCHAR(191) DEFAULT NULL, CHANGE last_name last_name VARCHAR(191) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
