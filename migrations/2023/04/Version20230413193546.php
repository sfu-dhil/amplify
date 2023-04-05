<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230413193546 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE FULLTEXT INDEX person_ft ON person (fullname, bio)');
        $this->addSql('CREATE FULLTEXT INDEX podcast_ft ON podcast (title, sub_title, description)');
        $this->addSql('CREATE FULLTEXT INDEX publisher_ft ON publisher (name, description)');
        $this->addSql('CREATE FULLTEXT INDEX institution_ft ON institution (name)');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX person_ft ON person');
        $this->addSql('DROP INDEX podcast_ft ON podcast');
        $this->addSql('DROP INDEX publisher_ft ON publisher');
        $this->addSql('DROP INDEX institution_ft ON institution');
    }
}
