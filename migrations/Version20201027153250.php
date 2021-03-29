<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201027153250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_language (id INT AUTO_INCREMENT NOT NULL, language VARCHAR(5) NOT NULL, user_id INT NOT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_by_id INT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_345695B5B03A8386 (created_by_id), INDEX IDX_345695B5896DBBDE (updated_by_id), INDEX user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_language ADD CONSTRAINT FK_345695B5B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_language ADD CONSTRAINT FK_345695B5896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('INSERT INTO user_language (`id`, `language`, `user_id`) VALUES (NULL, "en-us", "38248"), (NULL, "en-us", "38038"), (NULL, "en-us", "37847"), (NULL, "en-us", "38229"), (NULL, "en-us", "50734"), (NULL, "en-us", "51362");');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_language');
    }
}
