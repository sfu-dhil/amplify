<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200825222437 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE episode_language (episode_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_1D5D58C7362B62A0 (episode_id), INDEX IDX_1D5D58C782F1BAF4 (language_id), PRIMARY KEY(episode_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE episode_language ADD CONSTRAINT FK_1D5D58C7362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode_language ADD CONSTRAINT FK_1D5D58C782F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode DROP language');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE episode_language');
        $this->addSql('ALTER TABLE episode ADD language VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
