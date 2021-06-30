<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210623174010 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audio DROP FOREIGN KEY FK_187D3695362B62A0');
        $this->addSql('DROP INDEX UNIQ_187D3695362B62A0 ON audio');
        $this->addSql('ALTER TABLE audio ADD entity VARCHAR(80) NOT NULL;');
        $this->addSql("UPDATE audio SET entity=CONCAT('App\\\\Entity\\\\Episode:', `episode_id`);");
        $this->addSql('ALTER TABLE audio DROP episode_id');
    }

    public function down(Schema $schema) : void {
        $this->throwIrreversibleMigrationException();
    }
}
