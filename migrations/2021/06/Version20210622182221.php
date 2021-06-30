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
final class Version20210622182221 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE citation (id INT AUTO_INCREMENT NOT NULL, entity VARCHAR(128) NOT NULL, citation LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_FABD9C7EFABD9C7E6DE44026 (citation, description), INDEX IDX_FABD9C7EE284468 (entity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE link (id INT AUTO_INCREMENT NOT NULL, entity VARCHAR(128) NOT NULL, url VARCHAR(128) NOT NULL, text VARCHAR(128) DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_36AC99F1F47645AE3B8BA7C7 (url, text), INDEX IDX_36AC99F1E284468 (entity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE FULLTEXT INDEX IDX_C53D045F545615306DE44026 ON image (original_name, description)');
    }

    public function down(Schema $schema) : void {
        $this->throwIrreversibleMigrationException();
    }
}
