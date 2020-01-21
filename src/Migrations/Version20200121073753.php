<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200121073753 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tbl_trainee DROP FOREIGN KEY FK_1D958593D650898B');
        $this->addSql('ALTER TABLE tbl_trainer DROP FOREIGN KEY FK_9E4600541B9EE6AD');
        $this->addSql('CREATE TABLE tbl_sessionTrainee (id INT AUTO_INCREMENT NOT NULL, trainee_id INT NOT NULL, session_id INT NOT NULL, convocation VARCHAR(255) NOT NULL, INDEX IDX_88E8124C36C682D0 (trainee_id), INDEX IDX_88E8124C613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_sessionTrainer (id INT AUTO_INCREMENT NOT NULL, trainer_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_B3B978BFB08EDF6 (trainer_id), INDEX IDX_B3B978B613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tbl_sessionTrainee ADD CONSTRAINT FK_88E8124C36C682D0 FOREIGN KEY (trainee_id) REFERENCES tbl_trainee (id)');
        $this->addSql('ALTER TABLE tbl_sessionTrainee ADD CONSTRAINT FK_88E8124C613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id)');
        $this->addSql('ALTER TABLE tbl_sessionTrainer ADD CONSTRAINT FK_B3B978BFB08EDF6 FOREIGN KEY (trainer_id) REFERENCES tbl_trainer (id)');
        $this->addSql('ALTER TABLE tbl_sessionTrainer ADD CONSTRAINT FK_B3B978B613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id)');
        $this->addSql('DROP TABLE session_trainee');
        $this->addSql('DROP TABLE session_trainer');
        $this->addSql('ALTER TABLE tbl_session CHANGE start_date start_date DATE NOT NULL, CHANGE end_date end_date DATE NOT NULL, CHANGE comment comment VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_1D958593D650898B ON tbl_trainee');
        $this->addSql('ALTER TABLE tbl_trainee DROP session_trainee_id');
        $this->addSql('DROP INDEX IDX_9E4600541B9EE6AD ON tbl_trainer');
        $this->addSql('ALTER TABLE tbl_trainer DROP session_trainer_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE session_trainee (id INT AUTO_INCREMENT NOT NULL, sessions_id INT NOT NULL, document_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_541E0FBDF17C4D8C (sessions_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE session_trainer (id INT AUTO_INCREMENT NOT NULL, sessions_id INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_D7CD8A7AF17C4D8C (sessions_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE session_trainee ADD CONSTRAINT FK_541E0FBDF17C4D8C FOREIGN KEY (sessions_id) REFERENCES tbl_session (id)');
        $this->addSql('ALTER TABLE session_trainer ADD CONSTRAINT FK_D7CD8A7AF17C4D8C FOREIGN KEY (sessions_id) REFERENCES tbl_session (id)');
        $this->addSql('DROP TABLE tbl_sessionTrainee');
        $this->addSql('DROP TABLE tbl_sessionTrainer');
        $this->addSql('ALTER TABLE tbl_session CHANGE start_date start_date DATETIME NOT NULL, CHANGE end_date end_date DATETIME NOT NULL, CHANGE comment comment LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE tbl_trainee ADD session_trainee_id INT NOT NULL');
        $this->addSql('ALTER TABLE tbl_trainee ADD CONSTRAINT FK_1D958593D650898B FOREIGN KEY (session_trainee_id) REFERENCES session_trainee (id)');
        $this->addSql('CREATE INDEX IDX_1D958593D650898B ON tbl_trainee (session_trainee_id)');
        $this->addSql('ALTER TABLE tbl_trainer ADD session_trainer_id INT NOT NULL');
        $this->addSql('ALTER TABLE tbl_trainer ADD CONSTRAINT FK_9E4600541B9EE6AD FOREIGN KEY (session_trainer_id) REFERENCES session_trainer (id)');
        $this->addSql('CREATE INDEX IDX_9E4600541B9EE6AD ON tbl_trainer (session_trainer_id)');
    }
}
