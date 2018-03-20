<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180316084626 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE incident_severity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incident (id INT AUTO_INCREMENT NOT NULL, severity_id INT DEFAULT NULL, title VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, INDEX IDX_3D03A11AF7527401 (severity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE incident_application (incident_id INT NOT NULL, application_id INT NOT NULL, INDEX IDX_187393AC59E53FB9 (incident_id), INDEX IDX_187393AC3E030ACD (application_id), PRIMARY KEY(incident_id, application_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11AF7527401 FOREIGN KEY (severity_id) REFERENCES incident_severity (id)');
        $this->addSql('ALTER TABLE incident_application ADD CONSTRAINT FK_187393AC59E53FB9 FOREIGN KEY (incident_id) REFERENCES incident (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE incident_application ADD CONSTRAINT FK_187393AC3E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11AF7527401');
        $this->addSql('ALTER TABLE incident_application DROP FOREIGN KEY FK_187393AC59E53FB9');
        $this->addSql('DROP TABLE incident_severity');
        $this->addSql('DROP TABLE incident');
        $this->addSql('DROP TABLE incident_application');
    }
}
