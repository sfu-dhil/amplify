<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201111000301 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX institutions_uniq ON institution');
        $this->addSql('ALTER TABLE institution CHANGE province country VARCHAR(40) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX institutions_uniq ON institution (country, name)');
        $this->addSql('UPDATE institution SET country=\'Canada\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX institutions_uniq ON institution');
        $this->addSql('ALTER TABLE institution CHANGE country province VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX institutions_uniq ON institution (province, name)');
    }
}
