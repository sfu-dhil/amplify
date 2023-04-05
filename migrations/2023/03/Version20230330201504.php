<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330201504 extends AbstractMigration {
    public function getDescription() : string {
        return '';
    }

    public function up(Schema $schema) : void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nines_blog_page DROP FOREIGN KEY FK_23FD24C7A76ED395');
        $this->addSql('ALTER TABLE nines_blog_post DROP FOREIGN KEY FK_6D7DFE6A6BF700BD');
        $this->addSql('ALTER TABLE nines_blog_post DROP FOREIGN KEY FK_6D7DFE6AA76ED395');
        $this->addSql('ALTER TABLE nines_blog_post DROP FOREIGN KEY FK_6D7DFE6A12469DE2');
        $this->addSql('DROP TABLE nines_blog_page');
        $this->addSql('DROP TABLE nines_blog_post');
        $this->addSql('DROP TABLE nines_blog_post_category');
        $this->addSql('DROP TABLE nines_blog_post_status');
    }

    public function down(Schema $schema) : void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE nines_blog_page (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, weight INT NOT NULL, public TINYINT(1) NOT NULL, homepage TINYINT(1) DEFAULT 0 NOT NULL, include_comments TINYINT(1) NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, searchable LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', excerpt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, in_menu TINYINT(1) NOT NULL, INDEX IDX_23FD24C7A76ED395 (user_id), FULLTEXT INDEX blog_page_ft (title, searchable), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nines_blog_post (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, status_id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, include_comments TINYINT(1) NOT NULL, searchable LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', excerpt LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, FULLTEXT INDEX blog_post_ft (title, searchable), INDEX IDX_6D7DFE6A12469DE2 (category_id), INDEX IDX_6D7DFE6A6BF700BD (status_id), INDEX IDX_6D7DFE6AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nines_blog_post_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_32F5FC8CEA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_32F5FC8C5E237E06 (name), FULLTEXT INDEX IDX_32F5FC8CEA750E8 (label), FULLTEXT INDEX IDX_32F5FC8C6DE44026 (description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE nines_blog_post_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(191) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, label VARCHAR(200) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, public TINYINT(1) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', FULLTEXT INDEX IDX_4A63E2FDEA750E86DE44026 (label, description), UNIQUE INDEX UNIQ_4A63E2FD5E237E06 (name), FULLTEXT INDEX IDX_4A63E2FDEA750E8 (label), FULLTEXT INDEX IDX_4A63E2FD6DE44026 (description), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE nines_blog_page ADD CONSTRAINT FK_23FD24C7A76ED395 FOREIGN KEY (user_id) REFERENCES nines_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nines_blog_post ADD CONSTRAINT FK_6D7DFE6A6BF700BD FOREIGN KEY (status_id) REFERENCES nines_blog_post_status (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nines_blog_post ADD CONSTRAINT FK_6D7DFE6AA76ED395 FOREIGN KEY (user_id) REFERENCES nines_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nines_blog_post ADD CONSTRAINT FK_6D7DFE6A12469DE2 FOREIGN KEY (category_id) REFERENCES nines_blog_post_category (id) ON DELETE CASCADE');
    }
}
