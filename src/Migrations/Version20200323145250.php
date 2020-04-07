<?php

declare (strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200323145250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE application DROP icon, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE category_id category_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE description description VARCHAR(1000) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE application ADD icon LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, CHANGE category_id category_id INT DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
    }
}
