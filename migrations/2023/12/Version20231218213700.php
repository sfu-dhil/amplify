<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218213700 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contribution ADD roles JSON DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\', DROP role');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15786136AB');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15362B62A0');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E154EC001D1');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E154EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode CHANGE number number DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD40C86FCE');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD40C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA940C86FCE');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA940C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contribution ADD role VARCHAR(255) DEFAULT NULL, DROP roles');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15786136AB');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E154EC001D1');
        $this->addSql('ALTER TABLE contribution DROP FOREIGN KEY FK_EA351E15362B62A0');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E154EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE contribution ADD CONSTRAINT FK_EA351E15362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id)');
        $this->addSql('ALTER TABLE episode CHANGE number number INT NOT NULL');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA940C86FCE');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA940C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BD40C86FCE');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BD40C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
    }
}
