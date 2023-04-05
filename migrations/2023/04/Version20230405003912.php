<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405003912 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nines_media_audio DROP public');
        $this->addSql('ALTER TABLE nines_media_image DROP public');
        $this->addSql('ALTER TABLE nines_media_pdf DROP public');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nines_media_audio ADD public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE nines_media_image ADD public TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE nines_media_pdf ADD public TINYINT(1) NOT NULL');
    }
}
