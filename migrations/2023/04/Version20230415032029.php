<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415032029 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE export (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, format VARCHAR(255) NOT NULL, path VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_428C1694786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import (id INT AUTO_INCREMENT NOT NULL, podcast_id INT DEFAULT NULL, rss VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9D4ECE1D786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE export ADD CONSTRAINT FK_428C1694786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE export DROP FOREIGN KEY FK_428C1694786136AB');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1D786136AB');
        $this->addSql('DROP TABLE export');
        $this->addSql('DROP TABLE import');
    }
}
