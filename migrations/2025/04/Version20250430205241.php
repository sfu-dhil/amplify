<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430205241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contribution (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, podcast_id INT DEFAULT NULL, season_id INT DEFAULT NULL, episode_id INT DEFAULT NULL, roles JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EA351E15217BBB47 (person_id), INDEX IDX_EA351E15786136AB (podcast_id), INDEX IDX_EA351E154EC001D1 (season_id), INDEX IDX_EA351E15362B62A0 (episode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode (id INT AUTO_INCREMENT NOT NULL, season_id INT DEFAULT NULL, podcast_id INT NOT NULL, guid VARCHAR(255) DEFAULT NULL, episode_type VARCHAR(255) DEFAULT \'full\' NOT NULL, number DOUBLE PRECISION NOT NULL, date DATE NOT NULL, run_time VARCHAR(9) NOT NULL, title VARCHAR(255) NOT NULL, sub_title VARCHAR(255) DEFAULT NULL, explicit TINYINT(1) DEFAULT NULL, bibliography LONGTEXT DEFAULT NULL, transcript LONGTEXT DEFAULT NULL, description LONGTEXT NOT NULL, permissions LONGTEXT DEFAULT NULL, keywords JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DDAA1CDA4EC001D1 (season_id), INDEX IDX_DDAA1CDA786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE export (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, format VARCHAR(255) NOT NULL, path VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_428C1694786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import (id INT AUTO_INCREMENT NOT NULL, podcast_id INT DEFAULT NULL, user_id INT DEFAULT NULL, rss VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9D4ECE1D786136AB (podcast_id), INDEX IDX_9D4ECE1DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nines_media_audio (id INT AUTO_INCREMENT NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', entity VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, license LONGTEXT DEFAULT NULL, original_name LONGTEXT NOT NULL, path LONGTEXT NOT NULL, mime_type VARCHAR(64) NOT NULL, file_size INT NOT NULL, checksum VARCHAR(32) DEFAULT NULL, source_url LONGTEXT DEFAULT NULL, FULLTEXT INDEX nines_media_audio_ft (original_name, description), INDEX IDX_9D15F751E284468 (entity), INDEX IDX_9D15F751DE6FDF9A (checksum), FULLTEXT INDEX IDX_9D15F751A58240EF (source_url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nines_media_image (id INT AUTO_INCREMENT NOT NULL, thumb_path LONGTEXT NOT NULL, image_width INT NOT NULL, image_height INT NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', entity VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, license LONGTEXT DEFAULT NULL, original_name LONGTEXT NOT NULL, path LONGTEXT NOT NULL, mime_type VARCHAR(64) NOT NULL, file_size INT NOT NULL, checksum VARCHAR(32) DEFAULT NULL, source_url LONGTEXT DEFAULT NULL, FULLTEXT INDEX nines_media_image_ft (original_name, description), INDEX IDX_4055C59BE284468 (entity), INDEX IDX_4055C59BDE6FDF9A (checksum), FULLTEXT INDEX IDX_4055C59BA58240EF (source_url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nines_media_link (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(500) NOT NULL, text VARCHAR(191) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', entity VARCHAR(120) NOT NULL, FULLTEXT INDEX nines_media_link_ft (url, text), INDEX IDX_3B5D85A3E284468 (entity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nines_media_pdf (id INT AUTO_INCREMENT NOT NULL, original_name LONGTEXT NOT NULL, path LONGTEXT NOT NULL, mime_type VARCHAR(64) NOT NULL, file_size INT NOT NULL, checksum VARCHAR(32) DEFAULT NULL, source_url LONGTEXT DEFAULT NULL, thumb_path LONGTEXT NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', description LONGTEXT DEFAULT NULL, license LONGTEXT DEFAULT NULL, entity VARCHAR(120) NOT NULL, FULLTEXT INDEX nines_media_pdf_ft (original_name, description), INDEX IDX_9286B706E284468 (entity), INDEX IDX_9286B706DE6FDF9A (checksum), FULLTEXT INDEX IDX_9286B706A58240EF (source_url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nines_user (id INT AUTO_INCREMENT NOT NULL, active TINYINT(1) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_expiry DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', fullname VARCHAR(64) NOT NULL, affiliation VARCHAR(64) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', login DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5BA994A1E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, fullname VARCHAR(255) NOT NULL, sortable_name VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, bio LONGTEXT NOT NULL, institution VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_34DCD176786136AB (podcast_id), FULLTEXT INDEX person_ft (fullname, bio), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE podcast (id INT AUTO_INCREMENT NOT NULL, publisher_id INT DEFAULT NULL, guid VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, sub_title VARCHAR(255) DEFAULT NULL, explicit TINYINT(1) NOT NULL, description LONGTEXT NOT NULL, language_code VARCHAR(255) DEFAULT NULL, copyright LONGTEXT NOT NULL, license LONGTEXT DEFAULT NULL, website LONGTEXT NOT NULL, rss VARCHAR(255) NOT NULL, categories JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', keywords JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D7E805BD40C86FCE (publisher_id), FULLTEXT INDEX podcast_ft (title, sub_title, description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publisher (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, contact LONGTEXT NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9CE8D546786136AB (podcast_id), FULLTEXT INDEX publisher_ft (name, description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, publisher_id INT DEFAULT NULL, number INT DEFAULT NULL, title VARCHAR(255) NOT NULL, sub_title VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F0E45BA9786136AB (podcast_id), INDEX IDX_F0E45BA940C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE share (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, podcast_id INT NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EF069D5AA76ED395 (user_id), INDEX IDX_EF069D5A786136AB (podcast_id), UNIQUE INDEX shares_uniq (user_id, podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E154EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE export ADD CONSTRAINT FK_428C1694786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DA76ED395 FOREIGN KEY (user_id) REFERENCES nines_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD40C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE publisher ADD CONSTRAINT FK_9CE8D546786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA940C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5AA76ED395 FOREIGN KEY (user_id) REFERENCES nines_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15217BBB47');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15786136AB');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E154EC001D1');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15362B62A0');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA4EC001D1');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA786136AB');
        $this->addSql('ALTER TABLE export DROP FOREIGN KEY FK_428C1694786136AB');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1D786136AB');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1DA76ED395');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176786136AB');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD40C86FCE');
        $this->addSql('ALTER TABLE publisher DROP FOREIGN KEY FK_9CE8D546786136AB');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9786136AB');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA940C86FCE');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5AA76ED395');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A786136AB');
        $this->addSql('DROP TABLE contribution');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE export');
        $this->addSql('DROP TABLE import');
        $this->addSql('DROP TABLE nines_media_audio');
        $this->addSql('DROP TABLE nines_media_image');
        $this->addSql('DROP TABLE nines_media_link');
        $this->addSql('DROP TABLE nines_media_pdf');
        $this->addSql('DROP TABLE nines_user');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE podcast');
        $this->addSql('DROP TABLE publisher');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE share');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
