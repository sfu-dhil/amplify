<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230628205801 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode CHANGE subjects keywords LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE podcast ADD keywords LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode CHANGE keywords subjects LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE podcast DROP keywords');
    }
}
