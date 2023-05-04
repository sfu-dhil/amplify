<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525172955 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE export (id INT AUTO_INCREMENT NOT NULL, podcast_id INT NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, format VARCHAR(255) NOT NULL, path VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_428C1694786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import (id INT AUTO_INCREMENT NOT NULL, podcast_id INT DEFAULT NULL, rss VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, progress INT DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9D4ECE1D786136AB (podcast_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE export ADD CONSTRAINT FK_428C1694786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nines_blog_page DROP FOREIGN KEY FK_F4DA3AB0A76ED395');
        $this->addSql('ALTER TABLE nines_blog_post DROP FOREIGN KEY FK_BA5AE01D6BF700BD');
        $this->addSql('ALTER TABLE nines_blog_post DROP FOREIGN KEY FK_BA5AE01DA76ED395');
        $this->addSql('ALTER TABLE nines_blog_post DROP FOREIGN KEY FK_BA5AE01D12469DE2');
        $this->addSql('DROP TABLE nines_blog_page');
        $this->addSql('DROP TABLE nines_blog_post');
        $this->addSql('DROP TABLE nines_blog_post_category');
        $this->addSql('DROP TABLE nines_blog_post_status');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E1513CCE206');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15217BBB47');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E1513CCE206 FOREIGN KEY (contributor_role_id) REFERENCES contributor_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA4EC001D1');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA786136AB');
        $this->addSql('ALTER TABLE episode ADD guid VARCHAR(255) DEFAULT NULL, ADD episode_type VARCHAR(255) DEFAULT \'full\' NOT NULL, ADD explicit TINYINT(1) DEFAULT NULL, DROP preserved');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('CREATE FULLTEXT INDEX institution_ft ON institution (name)');
        $this->addSql('ALTER TABLE nines_media_audio ADD checksum VARCHAR(32) DEFAULT NULL, ADD source_url LONGTEXT DEFAULT NULL, DROP public');
        $this->addSql('CREATE INDEX IDX_9D15F751DE6FDF9A ON nines_media_audio (checksum)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_9D15F751A58240EF ON nines_media_audio (source_url)');
        $this->addSql('ALTER TABLE nines_media_image ADD checksum VARCHAR(32) DEFAULT NULL, ADD source_url LONGTEXT DEFAULT NULL, DROP public');
        $this->addSql('CREATE INDEX IDX_4055C59BDE6FDF9A ON nines_media_image (checksum)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_4055C59BA58240EF ON nines_media_image (source_url)');
        $this->addSql('ALTER TABLE nines_media_pdf ADD checksum VARCHAR(32) DEFAULT NULL, ADD source_url LONGTEXT DEFAULT NULL, DROP public');
        $this->addSql('CREATE INDEX IDX_9286B706DE6FDF9A ON nines_media_pdf (checksum)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_9286B706A58240EF ON nines_media_pdf (source_url)');
        $this->addSql('CREATE FULLTEXT INDEX person_ft ON person (fullname, bio)');
        $this->addSql('ALTER TABLE podcast ADD guid VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE FULLTEXT INDEX podcast_ft ON podcast (title, sub_title, description)');
        $this->addSql('CREATE FULLTEXT INDEX publisher_ft ON publisher (name, description)');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA940C86FCE');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9786136AB');
        $this->addSql('ALTER TABLE season DROP preserved');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA940C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE nines_blog_page (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, weight INT NOT NULL, public TINYINT(1) NOT NULL, homepage TINYINT(1) DEFAULT 0 NOT NULL, include_comments TINYINT(1) NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, searchable LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', excerpt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, in_menu TINYINT(1) NOT NULL, INDEX IDX_23FD24C7A76ED395 (user_id), FULLTEXT INDEX blog_page_ft (title, searchable), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nines_blog_post (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, status_id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, include_comments TINYINT(1) NOT NULL, searchable LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', excerpt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, FULLTEXT INDEX blog_post_ft (title, searchable), INDEX IDX_6D7DFE6A12469DE2 (category_id), INDEX IDX_6D7DFE6A6BF700BD (status_id), INDEX IDX_6D7DFE6AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nines_blog_post_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_32F5FC8CEA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_32F5FC8C5E237E06 (name), FULLTEXT INDEX IDX_32F5FC8CEA750E8 (label), FULLTEXT INDEX IDX_32F5FC8C6DE44026 (description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nines_blog_post_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, public TINYINT(1) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_4A63E2FDEA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_4A63E2FD5E237E06 (name), FULLTEXT INDEX IDX_4A63E2FDEA750E8 (label), FULLTEXT INDEX IDX_4A63E2FD6DE44026 (description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE nines_blog_page ADD CONSTRAINT FK_F4DA3AB0A76ED395 FOREIGN KEY (user_id) REFERENCES nines_user (id)');
        $this->addSql('ALTER TABLE nines_blog_post ADD CONSTRAINT FK_BA5AE01D6BF700BD FOREIGN KEY (status_id) REFERENCES nines_blog_post_status (id)');
        $this->addSql('ALTER TABLE nines_blog_post ADD CONSTRAINT FK_BA5AE01DA76ED395 FOREIGN KEY (user_id) REFERENCES nines_user (id)');
        $this->addSql('ALTER TABLE nines_blog_post ADD CONSTRAINT FK_BA5AE01D12469DE2 FOREIGN KEY (category_id) REFERENCES nines_blog_post_category (id)');
        $this->addSql('ALTER TABLE export DROP FOREIGN KEY FK_428C1694786136AB');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1D786136AB');
        $this->addSql('DROP TABLE export');
        $this->addSql('DROP TABLE import');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15217BBB47');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E1513CCE206');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E1513CCE206 FOREIGN KEY (contributor_role_id) REFERENCES contributor_role (id)');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA4EC001D1');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA786136AB');
        $this->addSql('ALTER TABLE episode ADD preserved TINYINT(1) NOT NULL, DROP guid, DROP episode_type, DROP explicit');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('DROP INDEX institution_ft ON institution');
        $this->addSql('DROP INDEX IDX_9D15F751DE6FDF9A ON nines_media_audio');
        $this->addSql('DROP INDEX IDX_9D15F751A58240EF ON nines_media_audio');
        $this->addSql('ALTER TABLE nines_media_audio ADD public TINYINT(1) NOT NULL, DROP checksum, DROP source_url');
        $this->addSql('DROP INDEX IDX_4055C59BDE6FDF9A ON nines_media_image');
        $this->addSql('DROP INDEX IDX_4055C59BA58240EF ON nines_media_image');
        $this->addSql('ALTER TABLE nines_media_image ADD public TINYINT(1) NOT NULL, DROP checksum, DROP source_url');
        $this->addSql('DROP INDEX IDX_9286B706DE6FDF9A ON nines_media_pdf');
        $this->addSql('DROP INDEX IDX_9286B706A58240EF ON nines_media_pdf');
        $this->addSql('ALTER TABLE nines_media_pdf ADD public TINYINT(1) NOT NULL, DROP checksum, DROP source_url');
        $this->addSql('DROP INDEX person_ft ON person');
        $this->addSql('DROP INDEX podcast_ft ON podcast');
        $this->addSql('ALTER TABLE podcast DROP guid');
        $this->addSql('DROP INDEX publisher_ft ON publisher');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9786136AB');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA940C86FCE');
        $this->addSql('ALTER TABLE season ADD preserved TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA940C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
    }
}
