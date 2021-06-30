<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210624182407 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audio ADD description LONGTEXT DEFAULT NULL, ADD license LONGTEXT DEFAULT NULL, CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE entity entity VARCHAR(128) NOT NULL, CHANGE audio_path path VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE blog_page CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE in_menu in_menu TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE blog_post CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE blog_post_category CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE blog_post_status CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE category CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE contribution CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE contributor_role CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE episode CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE subjects subjects LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE image CHANGE entity entity VARCHAR(128) NOT NULL, CHANGE original_name original_name VARCHAR(128) NOT NULL, CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image_path path VARCHAR(128) NOT NULL, CHANGE image_size file_size INT NOT NULL');
        $this->addSql('ALTER TABLE institution CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE language CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE person CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE podcast CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE publisher CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE season CHANGE created created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audio DROP description, DROP license, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL, CHANGE entity entity VARCHAR(80) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE path audio_path VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE blog_page CHANGE in_menu in_menu TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE blog_post CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE blog_post_category CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE blog_post_status CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE category CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE contribution CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE contributor_role CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE episode CHANGE subjects subjects LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE image CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL, CHANGE entity entity VARCHAR(80) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE original_name original_name VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE path image_path VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE file_size image_size INT NOT NULL');
        $this->addSql('ALTER TABLE institution CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE language CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE person CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE podcast CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE publisher CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE season CHANGE created created DATETIME NOT NULL, CHANGE updated updated DATETIME NOT NULL');
    }
}
