<?php declare(strict_types = 1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180315104013 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE account (employee_id INT NOT NULL, user_principal_name VARCHAR(255) NOT NULL, account_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_contact VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, generated_password VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7D3656A4E7927C74 (email), PRIMARY KEY(employee_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE security_audit (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE security_audit');
    }
}
