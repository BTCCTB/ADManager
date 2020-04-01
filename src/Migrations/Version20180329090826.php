<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180329090826 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE incident_severity (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_EA5B518B03A8386 (created_by_id), INDEX IDX_EA5B518896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incident (id INT AUTO_INCREMENT NOT NULL, severity_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_3D03A11AF7527401 (severity_id), INDEX IDX_3D03A11AB03A8386 (created_by_id), INDEX IDX_3D03A11A896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incident_application (incident_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_187393AC59E53FB9 (incident_id), INDEX IDX_187393AC3E030ACD (application_id), PRIMARY KEY(incident_id, application_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE incident_severity ADD CONSTRAINT FK_EA5B518B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident_severity ADD CONSTRAINT FK_EA5B518896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11AF7527401 FOREIGN KEY (severity_id) REFERENCES incident_severity (id)');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE incident_application ADD CONSTRAINT FK_187393AC59E53FB9 FOREIGN KEY (incident_id) REFERENCES incident (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE incident_application ADD CONSTRAINT FK_187393AC3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE application ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A45BDDC1B03A8386 ON application (created_by_id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC1896DBBDE ON application (updated_by_id)');
        $this->addSql('ALTER TABLE security_audit ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11AF7527401');
        $this->addSql('ALTER TABLE incident_application DROP FOREIGN KEY FK_187393AC59E53FB9');
        $this->addSql('DROP TABLE incident_severity');
        $this->addSql('DROP TABLE incident');
        $this->addSql('DROP TABLE incident_application');
        $this->addSql('ALTER TABLE account CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1B03A8386');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1896DBBDE');
        $this->addSql('DROP INDEX IDX_A45BDDC1B03A8386 ON application');
        $this->addSql('DROP INDEX IDX_A45BDDC1896DBBDE ON application');
        $this->addSql('ALTER TABLE application DROP created_by_id, DROP updated_by_id, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE security_audit DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL');
    }
}
