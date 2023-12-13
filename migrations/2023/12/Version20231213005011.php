<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231213005011 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E1513CCE206');
        $this->addSql('DROP TABLE contributor_role');
        $this->addSql('DROP INDEX IDX_EA351E1513CCE206 ON contribution');
        $this->addSql('ALTER TABLE contribution ADD role VARCHAR(255) DEFAULT NULL, DROP contributor_role_id');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contributor_role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', relator_term VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, FULLTEXT INDEX IDX_8421DF24EA750E8 (label), FULLTEXT INDEX IDX_8421DF246DE44026 (description), FULLTEXT INDEX IDX_8421DF24EA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_8421DF245E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contribution ADD contributor_role_id INT NOT NULL, DROP role');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E1513CCE206 FOREIGN KEY (contributor_role_id) REFERENCES contributor_role (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_EA351E1513CCE206 ON contribution (contributor_role_id)');
    }
}
