<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190425095356 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE incident_severity CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE loggable_entry CHANGE object_id object_id VARCHAR(64) DEFAULT NULL, CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE incident CHANGE severity_id severity_id INT DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE end_date end_date DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE account CHANGE generated_password generated_password VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE application CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE category_id category_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE security_audit CHANGE object_id object_id VARCHAR(64) DEFAULT NULL, CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE expired_at expired_at DATE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account CHANGE generated_password generated_password VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE application CHANGE category_id category_id INT DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE incident CHANGE severity_id severity_id INT DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE end_date end_date DATETIME DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE incident_severity CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loggable_entry CHANGE object_id object_id VARCHAR(64) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE data data LONGTEXT DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE security_audit CHANGE object_id object_id VARCHAR(64) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE data data LONGTEXT DEFAULT \'NULL\' COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE password password VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8_unicode_ci, CHANGE roles roles JSON DEFAULT \'NULL\' COLLATE utf8mb4_bin COMMENT \'(DC2Type:json_array)\', CHANGE expired_at expired_at DATE DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
    }
}
