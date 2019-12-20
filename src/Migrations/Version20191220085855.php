<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191220085855 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE incident_application DROP FOREIGN KEY FK_187393AC59E53FB9');
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11AF7527401');
        $this->addSql('DROP TABLE incident');
        $this->addSql('DROP TABLE incident_application');
        $this->addSql('DROP TABLE incident_severity');
        $this->addSql('ALTER TABLE account CHANGE generated_password generated_password VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE last_login_at last_login_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE loggable_entry CHANGE object_id object_id VARCHAR(64) DEFAULT NULL, CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE application CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE category_id category_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE security_audit CHANGE object_id object_id VARCHAR(64) DEFAULT NULL, CHANGE data data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE roles roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE expired_at expired_at DATE DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE incident (id INT AUTO_INCREMENT NOT NULL, severity_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(100) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, description LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT \'NULL\', created_at DATETIME DEFAULT \'NULL\', updated_at DATETIME DEFAULT \'NULL\', INDEX IDX_3D03A11AB03A8386 (created_by_id), INDEX IDX_3D03A11A896DBBDE (updated_by_id), INDEX IDX_3D03A11AF7527401 (severity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE incident_application (incident_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_187393AC59E53FB9 (incident_id), INDEX IDX_187393AC3E030ACD (application_id), PRIMARY KEY(incident_id, application_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE incident_severity (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, `label` VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME DEFAULT \'NULL\', updated_at DATETIME DEFAULT \'NULL\', INDEX IDX_EA5B518896DBBDE (updated_by_id), INDEX IDX_EA5B518B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11AF7527401 FOREIGN KEY (severity_id) REFERENCES incident_severity (id)');
        $this->addSql('ALTER TABLE incident_application ADD CONSTRAINT FK_187393AC3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE incident_application ADD CONSTRAINT FK_187393AC59E53FB9 FOREIGN KEY (incident_id) REFERENCES incident (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE incident_severity ADD CONSTRAINT FK_EA5B518896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident_severity ADD CONSTRAINT FK_EA5B518B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE account CHANGE generated_password generated_password VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE last_login_at last_login_at DATETIME DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE application CHANGE category_id category_id INT DEFAULT NULL, CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE updated_by_id updated_by_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE loggable_entry CHANGE object_id object_id VARCHAR(64) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE data data LONGTEXT CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`');
        $this->addSql('ALTER TABLE security_audit CHANGE object_id object_id VARCHAR(64) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE data data LONGTEXT CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:array)\', CHANGE username username VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE password password VARCHAR(255) CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci`, CHANGE roles roles LONGTEXT CHARACTER SET utf8 DEFAULT \'NULL\' COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:json_array)\', CHANGE expired_at expired_at DATE DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
    }
}
