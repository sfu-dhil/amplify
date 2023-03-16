<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308185221 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nines_media_audio ADD source_url LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE FULLTEXT INDEX IDX_9D15F751A58240EF ON nines_media_audio (source_url)');
        $this->addSql('ALTER TABLE nines_media_image ADD source_url LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE FULLTEXT INDEX IDX_4055C59BA58240EF ON nines_media_image (source_url)');
        $this->addSql('ALTER TABLE nines_media_pdf ADD source_url LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE FULLTEXT INDEX IDX_9286B706A58240EF ON nines_media_pdf (source_url)');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_9D15F751A58240EF ON nines_media_audio');
        $this->addSql('ALTER TABLE nines_media_audio DROP source_url');
        $this->addSql('DROP INDEX IDX_4055C59BA58240EF ON nines_media_image');
        $this->addSql('ALTER TABLE nines_media_image DROP source_url');
        $this->addSql('DROP INDEX IDX_9286B706A58240EF ON nines_media_pdf');
        $this->addSql('ALTER TABLE nines_media_pdf DROP source_url');
    }
}
