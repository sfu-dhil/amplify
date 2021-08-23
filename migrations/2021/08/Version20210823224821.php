<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210823224821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS citation');
        $this->addSql('INSERT INTO link(entity,url, created, updated) select concat(\'App\\\\Entity\\\\Person:\', id), regexp_replace(links, "^[^\\"]*\\"|\\"[^\\"]*$", ""), now(), now() from person;');
        $this->addSql('ALTER TABLE person DROP IF EXISTS links');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
