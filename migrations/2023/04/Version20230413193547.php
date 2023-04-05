<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230413193547 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE export');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE export (id INT AUTO_INCREMENT NOT NULL, season_id INT NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, format VARCHAR(255) NOT NULL, path VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_428C16944EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE export ADD CONSTRAINT FK_428C16944EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
    }
}
