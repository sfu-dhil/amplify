<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240522175633 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nines_media_image CHANGE thumb_path thumb_path LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE nines_media_pdf CHANGE thumb_path thumb_path LONGTEXT NOT NULL');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nines_media_image CHANGE thumb_path thumb_path VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE nines_media_pdf CHANGE thumb_path thumb_path VARCHAR(128) NOT NULL');
    }
}
