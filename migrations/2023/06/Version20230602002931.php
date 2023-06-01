<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602002931 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA82F1BAF4');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD82F1BAF4');
        $this->addSql('ALTER TABLE podcast_category DROP FOREIGN KEY FK_E633B1E812469DE2');
        $this->addSql('ALTER TABLE podcast_category DROP FOREIGN KEY FK_E633B1E8786136AB');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE podcast_category');
        $this->addSql('DROP INDEX IDX_DDAA1CDA82F1BAF4 ON episode');
        $this->addSql('ALTER TABLE episode DROP language_id, CHANGE subjects subjects LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('DROP INDEX IDX_D7E805BD82F1BAF4 ON podcast');
        $this->addSql('ALTER TABLE podcast ADD language_code VARCHAR(255) DEFAULT NULL, ADD categories LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', DROP language_id');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_64C19C1EA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_64C19C15E237E06 (name), FULLTEXT INDEX IDX_64C19C1EA750E8 (label), FULLTEXT INDEX IDX_64C19C16DE44026 (description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_D4DB71B56DE44026 (description), FULLTEXT INDEX IDX_D4DB71B5EA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_D4DB71B55E237E06 (name), FULLTEXT INDEX IDX_D4DB71B5EA750E8 (label), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE podcast_category (podcast_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E633B1E812469DE2 (category_id), INDEX IDX_E633B1E8786136AB (podcast_id), PRIMARY KEY(podcast_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE podcast_category ADD CONSTRAINT FK_E633B1E812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast_category ADD CONSTRAINT FK_E633B1E8786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode ADD language_id INT DEFAULT NULL, CHANGE subjects subjects LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('CREATE INDEX IDX_DDAA1CDA82F1BAF4 ON episode (language_id)');
        $this->addSql('ALTER TABLE podcast ADD language_id INT DEFAULT NULL, DROP language_code, DROP categories');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('CREATE INDEX IDX_D7E805BD82F1BAF4 ON podcast (language_id)');
    }
}
