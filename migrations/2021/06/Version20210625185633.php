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
final class Version20210625185633 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pdf (id INT AUTO_INCREMENT NOT NULL, thumb_path VARCHAR(128) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', entity VARCHAR(128) NOT NULL, description LONGTEXT DEFAULT NULL, license LONGTEXT DEFAULT NULL, public TINYINT(1) NOT NULL, original_name VARCHAR(128) NOT NULL, path VARCHAR(128) NOT NULL, mime_type VARCHAR(64) NOT NULL, file_size INT NOT NULL, FULLTEXT INDEX IDX_EF0DB8C545615306DE44026 (original_name, description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void {
        $this->throwIrreversibleMigrationException();
    }
}
