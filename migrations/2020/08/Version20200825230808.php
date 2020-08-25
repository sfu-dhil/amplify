<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200825230808 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, label VARCHAR(120) NOT NULL, description LONGTEXT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, FULLTEXT INDEX IDX_64C19C1EA750E8 (label), FULLTEXT INDEX IDX_64C19C16DE44026 (description), FULLTEXT INDEX IDX_64C19C1EA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_64C19C15E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE podcast_category (podcast_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E633B1E8786136AB (podcast_id), INDEX IDX_E633B1E812469DE2 (category_id), PRIMARY KEY(podcast_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE podcast_category ADD CONSTRAINT FK_E633B1E8786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast_category ADD CONSTRAINT FK_E633B1E812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast DROP category');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE podcast_category DROP FOREIGN KEY FK_E633B1E812469DE2');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE podcast_category');
        $this->addSql('ALTER TABLE podcast ADD category VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
