<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218213930 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM person');
        $this->addSql('DELETE FROM publisher');
        $this->addSql('ALTER TABLE person ADD podcast_id INT NOT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_34DCD176786136AB ON person (podcast_id)');
        $this->addSql('ALTER TABLE publisher ADD podcast_id INT NOT NULL');
        $this->addSql('ALTER TABLE publisher ADD CONSTRAINT FK_9CE8D546786136AB FOREIGN KEY (podcast_id) REFERENCES podcast (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9CE8D546786136AB ON publisher (podcast_id)');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publisher DROP FOREIGN KEY FK_9CE8D546786136AB');
        $this->addSql('DROP INDEX IDX_9CE8D546786136AB ON publisher');
        $this->addSql('ALTER TABLE publisher DROP podcast_id');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176786136AB');
        $this->addSql('DROP INDEX IDX_34DCD176786136AB ON person');
        $this->addSql('ALTER TABLE person DROP podcast_id');
    }
}
