<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200721175703 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contribution (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, contributor_role_id INT NOT NULL, podcast_id INT DEFAULT NULL, season_id INT DEFAULT NULL, episode_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_EA351E15217BBB47 (person_id), INDEX IDX_EA351E1513CCE206 (contributor_role_id), INDEX IDX_EA351E15786136AB (podcast_id), INDEX IDX_EA351E154EC001D1 (season_id), INDEX IDX_EA351E15362B62A0 (episode_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contributor_role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, label VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, FULLTEXT INDEX IDX_8421DF24EA750E8 (label), FULLTEXT INDEX IDX_8421DF246DE44026 (description), FULLTEXT INDEX IDX_8421DF24EA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_8421DF245E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode (id INT AUTO_INCREMENT NOT NULL, season_id INT DEFAULT NULL, podcast_id INT NOT NULL, number INT NOT NULL, date DATE NOT NULL, run_time INT NOT NULL, title VARCHAR(255) NOT NULL, alternative_title VARCHAR(255) DEFAULT NULL, language VARCHAR(32) NOT NULL, tags LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', `references` LONGTEXT NOT NULL, copyright LONGTEXT NOT NULL, transcript LONGTEXT NOT NULL, abstract LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_DDAA1CDA4EC001D1 (season_id), INDEX IDX_DDAA1CDA786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode_subject (episode_id INT NOT NULL, subject_id INT NOT NULL, INDEX IDX_71C7CA09362B62A0 (episode_id), INDEX IDX_71C7CA0923EDC87 (subject_id), PRIMARY KEY(episode_id, subject_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, fullname VARCHAR(255) NOT NULL, sortable_name VARCHAR(255) NOT NULL, bio LONGTEXT NOT NULL, links LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE podcast (id INT AUTO_INCREMENT NOT NULL, publisher_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, alternative_title VARCHAR(255) DEFAULT NULL, explicit TINYINT(1) NOT NULL, description LONGTEXT NOT NULL, copyright LONGTEXT NOT NULL, category VARCHAR(255) NOT NULL, website LONGTEXT NOT NULL, rss VARCHAR(255) NOT NULL, tags LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_D7E805BD40C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publisher (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, contact LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, publisher_id INT DEFAULT NULL, number INT DEFAULT NULL, title VARCHAR(255) NOT NULL, alternative_title VARCHAR(255) DEFAULT NULL, description LONGTEXT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_F0E45BA9786136AB (podcast_id), INDEX IDX_F0E45BA940C86FCE (publisher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subject (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, label VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, FULLTEXT INDEX IDX_FBCE3E7AEA750E8 (label), FULLTEXT INDEX IDX_FBCE3E7A6DE44026 (description), FULLTEXT INDEX IDX_FBCE3E7AEA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_FBCE3E7A5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E1513CCE206 FOREIGN KEY (contributor_role_id) REFERENCES contributor_role (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E154EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id)');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('ALTER TABLE episode_subject ADD CONSTRAINT FK_71C7CA09362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode_subject ADD CONSTRAINT FK_71C7CA0923EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD40C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA940C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E1513CCE206');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15362B62A0');
        $this->addSql('ALTER TABLE episode_subject DROP FOREIGN KEY FK_71C7CA09362B62A0');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15217BBB47');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15786136AB');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA786136AB');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9786136AB');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD40C86FCE');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA940C86FCE');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E154EC001D1');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA4EC001D1');
        $this->addSql('ALTER TABLE episode_subject DROP FOREIGN KEY FK_71C7CA0923EDC87');
        $this->addSql('DROP TABLE contribution');
        $this->addSql('DROP TABLE contributor_role');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE episode_subject');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE podcast');
        $this->addSql('DROP TABLE publisher');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE subject');
    }
}
