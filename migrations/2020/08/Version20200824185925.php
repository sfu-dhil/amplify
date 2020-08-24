<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200824185925 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE institution (id INT AUTO_INCREMENT NOT NULL, province VARCHAR(60) NOT NULL, name VARCHAR(200) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX institutions_uniq ON institution (province, name)');
        $this->addSql('ALTER TABLE episode ADD preserved TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE season ADD preserved TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX institutions_uniq ON institution');
        $this->addSql('DROP TABLE institution');
        $this->addSql('ALTER TABLE episode DROP preserved');
        $this->addSql('ALTER TABLE season DROP preserved');
    }
}
