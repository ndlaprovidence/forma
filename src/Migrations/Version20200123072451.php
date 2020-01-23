<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200123072451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tbl_company (id INT AUTO_INCREMENT NOT NULL, corporate_name VARCHAR(255) NOT NULL, street VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, siret_number VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_goal (id INT AUTO_INCREMENT NOT NULL, title LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_instructor (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_session (id INT AUTO_INCREMENT NOT NULL, training_id INT NOT NULL, location_id INT NOT NULL, trainee_participation_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_8B17DDA0BEFD98D1 (training_id), INDEX IDX_8B17DDA064D218E (location_id), INDEX IDX_8B17DDA060BEE2B4 (trainee_participation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_instructor (session_id INT NOT NULL, instructor_id INT NOT NULL, INDEX IDX_D16A4886613FECDF (session_id), INDEX IDX_D16A48868C4FC193 (instructor_id), PRIMARY KEY(session_id, instructor_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_sessionLocation (id INT AUTO_INCREMENT NOT NULL, street VARCHAR(255) NOT NULL, postal_code INT NOT NULL, city VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_trainee (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_1D958593979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainee_participation (id INT AUTO_INCREMENT NOT NULL, trainee_id INT NOT NULL, convocation VARCHAR(255) NOT NULL, INDEX IDX_844835F36C682D0 (trainee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_training (id INT AUTO_INCREMENT NOT NULL, training_category_id INT NOT NULL, title VARCHAR(255) NOT NULL, platform VARCHAR(255) NOT NULL, INDEX IDX_82216CA2B62DE735 (training_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training_goal (training_id INT NOT NULL, goal_id INT NOT NULL, INDEX IDX_F346AC6DBEFD98D1 (training_id), INDEX IDX_F346AC6D667D1AFE (goal_id), PRIMARY KEY(training_id, goal_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_trainingCategory (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tbl_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_38B383A1E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tbl_session ADD CONSTRAINT FK_8B17DDA0BEFD98D1 FOREIGN KEY (training_id) REFERENCES tbl_training (id)');
        $this->addSql('ALTER TABLE tbl_session ADD CONSTRAINT FK_8B17DDA064D218E FOREIGN KEY (location_id) REFERENCES tbl_sessionLocation (id)');
        $this->addSql('ALTER TABLE tbl_session ADD CONSTRAINT FK_8B17DDA060BEE2B4 FOREIGN KEY (trainee_participation_id) REFERENCES trainee_participation (id)');
        $this->addSql('ALTER TABLE session_instructor ADD CONSTRAINT FK_D16A4886613FECDF FOREIGN KEY (session_id) REFERENCES tbl_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_instructor ADD CONSTRAINT FK_D16A48868C4FC193 FOREIGN KEY (instructor_id) REFERENCES tbl_instructor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tbl_trainee ADD CONSTRAINT FK_1D958593979B1AD6 FOREIGN KEY (company_id) REFERENCES tbl_company (id)');
        $this->addSql('ALTER TABLE trainee_participation ADD CONSTRAINT FK_844835F36C682D0 FOREIGN KEY (trainee_id) REFERENCES tbl_trainee (id)');
        $this->addSql('ALTER TABLE tbl_training ADD CONSTRAINT FK_82216CA2B62DE735 FOREIGN KEY (training_category_id) REFERENCES tbl_trainingCategory (id)');
        $this->addSql('ALTER TABLE training_goal ADD CONSTRAINT FK_F346AC6DBEFD98D1 FOREIGN KEY (training_id) REFERENCES tbl_training (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE training_goal ADD CONSTRAINT FK_F346AC6D667D1AFE FOREIGN KEY (goal_id) REFERENCES tbl_goal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tbl_trainee DROP FOREIGN KEY FK_1D958593979B1AD6');
        $this->addSql('ALTER TABLE training_goal DROP FOREIGN KEY FK_F346AC6D667D1AFE');
        $this->addSql('ALTER TABLE session_instructor DROP FOREIGN KEY FK_D16A48868C4FC193');
        $this->addSql('ALTER TABLE session_instructor DROP FOREIGN KEY FK_D16A4886613FECDF');
        $this->addSql('ALTER TABLE tbl_session DROP FOREIGN KEY FK_8B17DDA064D218E');
        $this->addSql('ALTER TABLE trainee_participation DROP FOREIGN KEY FK_844835F36C682D0');
        $this->addSql('ALTER TABLE tbl_session DROP FOREIGN KEY FK_8B17DDA060BEE2B4');
        $this->addSql('ALTER TABLE tbl_session DROP FOREIGN KEY FK_8B17DDA0BEFD98D1');
        $this->addSql('ALTER TABLE training_goal DROP FOREIGN KEY FK_F346AC6DBEFD98D1');
        $this->addSql('ALTER TABLE tbl_training DROP FOREIGN KEY FK_82216CA2B62DE735');
        $this->addSql('DROP TABLE tbl_company');
        $this->addSql('DROP TABLE tbl_goal');
        $this->addSql('DROP TABLE tbl_instructor');
        $this->addSql('DROP TABLE tbl_session');
        $this->addSql('DROP TABLE session_instructor');
        $this->addSql('DROP TABLE tbl_sessionLocation');
        $this->addSql('DROP TABLE tbl_trainee');
        $this->addSql('DROP TABLE trainee_participation');
        $this->addSql('DROP TABLE tbl_training');
        $this->addSql('DROP TABLE training_goal');
        $this->addSql('DROP TABLE tbl_trainingCategory');
        $this->addSql('DROP TABLE tbl_user');
    }
}