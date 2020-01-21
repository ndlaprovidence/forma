<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200121074716 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE session_trainee (id INT AUTO_INCREMENT NOT NULL, trainee_id INT NOT NULL, session_id INT NOT NULL, convocation VARCHAR(255) NOT NULL, INDEX IDX_541E0FBD36C682D0 (trainee_id), INDEX IDX_541E0FBD613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_trainer (id INT AUTO_INCREMENT NOT NULL, trainer_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_D7CD8A7AFB08EDF6 (trainer_id), INDEX IDX_D7CD8A7A613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE session_trainee ADD CONSTRAINT FK_541E0FBD36C682D0 FOREIGN KEY (trainee_id) REFERENCES tbl_trainee (id)');
        $this->addSql('ALTER TABLE session_trainee ADD CONSTRAINT FK_541E0FBD613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id)');
        $this->addSql('ALTER TABLE session_trainer ADD CONSTRAINT FK_D7CD8A7AFB08EDF6 FOREIGN KEY (trainer_id) REFERENCES tbl_trainer (id)');
        $this->addSql('ALTER TABLE session_trainer ADD CONSTRAINT FK_D7CD8A7A613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id)');
        $this->addSql('DROP TABLE tbl_sessiontrainee');
        $this->addSql('DROP TABLE tbl_sessiontrainer');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tbl_sessiontrainee (id INT AUTO_INCREMENT NOT NULL, trainee_id INT NOT NULL, session_id INT NOT NULL, convocation VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_88E8124C613FECDF (session_id), INDEX IDX_88E8124C36C682D0 (trainee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tbl_sessiontrainer (id INT AUTO_INCREMENT NOT NULL, trainer_id INT NOT NULL, session_id INT NOT NULL, INDEX IDX_B3B978B613FECDF (session_id), INDEX IDX_B3B978BFB08EDF6 (trainer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tbl_sessiontrainee ADD CONSTRAINT FK_88E8124C36C682D0 FOREIGN KEY (trainee_id) REFERENCES tbl_trainee (id)');
        $this->addSql('ALTER TABLE tbl_sessiontrainee ADD CONSTRAINT FK_88E8124C613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id)');
        $this->addSql('ALTER TABLE tbl_sessiontrainer ADD CONSTRAINT FK_B3B978B613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id)');
        $this->addSql('ALTER TABLE tbl_sessiontrainer ADD CONSTRAINT FK_B3B978BFB08EDF6 FOREIGN KEY (trainer_id) REFERENCES tbl_trainer (id)');
        $this->addSql('DROP TABLE session_trainee');
        $this->addSql('DROP TABLE session_trainer');
    }
}
