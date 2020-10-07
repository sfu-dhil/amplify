<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201007213829 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode_subject DROP FOREIGN KEY FK_71C7CA0923EDC87');
        $this->addSql('DROP TABLE episode_subject');
        $this->addSql('DROP TABLE subject');
        $this->addSql('ALTER TABLE episode ADD subjects JSON NOT NULL');
        $this->addSql('UPDATE episode SET subjects=\'{}\'');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
