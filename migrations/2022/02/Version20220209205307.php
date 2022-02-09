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
final class Version20220209205307 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('RENAME TABLE audio TO nines_media_audio');
        $this->addSql('RENAME TABLE blog_page TO nines_blog_page');
        $this->addSql('RENAME TABLE blog_post TO nines_blog_post');
        $this->addSql('RENAME TABLE blog_post_category TO nines_blog_post_category');
        $this->addSql('RENAME TABLE blog_post_status TO nines_blog_post_status');
        $this->addSql('RENAME TABLE image TO nines_media_image');
        $this->addSql('RENAME TABLE link TO nines_media_link');
        $this->addSql('RENAME TABLE pdf TO nines_media_pdf');
    }

    public function down(Schema $schema) : void {
        $this->throwIrreversibleMigrationException();
    }
}
