<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171222106000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("INSERT INTO application (name, link) VALUES ('GO4HR', 'https://performancemanager5.successfactors.eu/login?company=C0000960484P')");
        $this->addSql("INSERT INTO application (name, link) VALUES ('GOFAST', 'https://documents.enabel.be/')");
        $this->addSql("INSERT INTO application (name, link, link_fr, link_nl) VALUES (\"Who's Who\", 'https://intranet.enabel.be/en/whoiswho', 'https://intranet.enabel.be/fr/whoiswho', 'https://intranet.enabel.be/nl/whoiswho')");
        $this->addSql("INSERT INTO application (name, link, link_fr, link_nl) VALUES (\"Myworkandme\", 'https://www.myworkandme.com/site6/en/Portal/MyWorkAndMe/Start.aspx', 'https://www.myworkandme.com/site6/fr-BE/Portal/MyWorkAndMe/Start.aspx', 'https://www.myworkandme.com/site6/nl-BE/Portal/MyWorkAndMe/Start.aspx')");
        $this->addSql("INSERT INTO application (name, link, link_fr, link_nl) VALUES (\"Integrity\", 'https://intranet.enabel.be/fr/content/integrite', 'https://intranet.enabel.be/fr/content/integrite', 'https://intranet.enabel.be/nl/content/integriteit')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Webforms\", 'https://webforms.enabel.be/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"BAT\", 'https://intranet.enabel.be/BAT/LoginController?do=gotoIndexPage')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Pitweb\", 'http://pitweb.enabel.be/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Timesheet\", 'https://timesheet.enabel.be/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Webmail Office 365\", 'https://outlook.office.com')");
        $this->addSql("INSERT INTO application (name, link, enable) VALUES (\"Webmail HQ\", 'https://domino.btcctb.org/', 0)");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Claroline\", 'https://intranet.enabel.be/claroline/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Damino\", 'https://damino.enabel.be')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Filecloud\", 'https://filecloud.enabel.be')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Mandates\", 'https://mandate.enabel.be/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"OpenEnabel\", 'https://open.enabel.be/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"Parking\", 'https://intranet.enabel.be/servicecenter/')");
        $this->addSql("INSERT INTO application (name, link) VALUES (\"OLD Webmail FIELD\", 'https://webmail.btcctb.org')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE TABLE application');
    }
}
