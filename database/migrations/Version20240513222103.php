<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240513222103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'chore: remove competition_register tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE competition_register DROP FOREIGN KEY FK_6E887B3B6C1129CD');
        $this->addSql('ALTER TABLE competition_register_departure DROP FOREIGN KEY FK_B1C7EA7C61D76400');
        $this->addSql('ALTER TABLE competition_register_departure_target DROP FOREIGN KEY FK_DDB057F07704ED06');
        $this->addSql('ALTER TABLE competition_register_departure_target_archer DROP FOREIGN KEY FK_70910F31158E0B66');
        $this->addSql('DROP TABLE competition_register');
        $this->addSql('DROP TABLE competition_register_departure');
        $this->addSql('DROP TABLE competition_register_departure_target');
        $this->addSql('DROP TABLE competition_register_departure_target_archer');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE competition_register (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', mandate_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', date_start DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_end DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', types LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', slug VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6E887B3B6C1129CD (mandate_id), UNIQUE INDEX UNIQ_6E887B3B989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE competition_register_departure (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', competition_register_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', max_registration INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B1C7EA7C61D76400 (competition_register_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE competition_register_departure_target (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', departure_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, distance INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_DDB057F07704ED06 (departure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE competition_register_departure_target_archer (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', target_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', club VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, wheelchair TINYINT(1) NOT NULL, first_year TINYINT(1) NOT NULL, additional_information LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, position VARCHAR(127) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, first_name VARCHAR(191) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, last_name VARCHAR(191) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(191) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(12) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, license_number VARCHAR(8) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, gender VARCHAR(191) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, category VARCHAR(191) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, weapon VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, paid TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX unique_archer_by_target (target_id, license_number), INDEX IDX_70910F31158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE competition_register ADD CONSTRAINT FK_6E887B3B6C1129CD FOREIGN KEY (mandate_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE competition_register_departure ADD CONSTRAINT FK_B1C7EA7C61D76400 FOREIGN KEY (competition_register_id) REFERENCES competition_register (id)');
        $this->addSql('ALTER TABLE competition_register_departure_target ADD CONSTRAINT FK_DDB057F07704ED06 FOREIGN KEY (departure_id) REFERENCES competition_register_departure (id)');
        $this->addSql('ALTER TABLE competition_register_departure_target_archer ADD CONSTRAINT FK_70910F31158E0B66 FOREIGN KEY (target_id) REFERENCES competition_register_departure_target (id)');
    }
}
