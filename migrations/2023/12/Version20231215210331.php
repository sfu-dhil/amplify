<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231215210331 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('TRUNCATE TABLE contribution');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17610405986');
        $this->addSql('DROP TABLE institution');
        $this->addSql('ALTER TABLE episode CHANGE keywords keywords JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE status status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX IDX_34DCD17610405986 ON person');
        $this->addSql('ALTER TABLE person ADD institution VARCHAR(255) DEFAULT NULL, DROP institution_id');
        $this->addSql('ALTER TABLE podcast CHANGE categories categories JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE keywords keywords JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE status status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE season CHANGE status status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE institution (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX institution_ft (name), UNIQUE INDEX institutions_uniq (country, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE episode CHANGE keywords keywords JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE status status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE person ADD institution_id INT DEFAULT NULL, DROP institution');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17610405986 FOREIGN KEY (institution_id) REFERENCES institution (id)');
        $this->addSql('CREATE INDEX IDX_34DCD17610405986 ON person (institution_id)');
        $this->addSql('ALTER TABLE season CHANGE status status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE podcast CHANGE categories categories JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE keywords keywords JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', CHANGE status status JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
