<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230307190310 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode ADD guid VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE nines_media_audio ADD checksum VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_9D15F751DE6FDF9A ON nines_media_audio (checksum)');
        $this->addSql('ALTER TABLE nines_media_image ADD checksum VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_4055C59BDE6FDF9A ON nines_media_image (checksum)');
        $this->addSql('ALTER TABLE nines_media_pdf ADD checksum VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_9286B706DE6FDF9A ON nines_media_pdf (checksum)');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode DROP guid');
        $this->addSql('DROP INDEX IDX_9D15F751DE6FDF9A ON nines_media_audio');
        $this->addSql('ALTER TABLE nines_media_audio DROP checksum');
        $this->addSql('DROP INDEX IDX_4055C59BDE6FDF9A ON nines_media_image');
        $this->addSql('ALTER TABLE nines_media_image DROP checksum');
        $this->addSql('DROP INDEX IDX_9286B706DE6FDF9A ON nines_media_pdf');
        $this->addSql('ALTER TABLE nines_media_pdf DROP checksum');
    }
}
