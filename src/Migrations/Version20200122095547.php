<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200122095547 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE upload (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tbl_session ADD upload_id INT NOT NULL, DROP data_file');
        $this->addSql('ALTER TABLE tbl_session ADD CONSTRAINT FK_8B17DDA0CCCFBA31 FOREIGN KEY (upload_id) REFERENCES upload (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B17DDA0CCCFBA31 ON tbl_session (upload_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tbl_session DROP FOREIGN KEY FK_8B17DDA0CCCFBA31');
        $this->addSql('DROP TABLE upload');
        $this->addSql('DROP INDEX UNIQ_8B17DDA0CCCFBA31 ON tbl_session');
        $this->addSql('ALTER TABLE tbl_session ADD data_file VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP upload_id');
    }
}
