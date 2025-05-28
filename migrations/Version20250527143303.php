<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527143303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE child_presence (id INT AUTO_INCREMENT NOT NULL, semainier_id INT NOT NULL, day DATETIME NOT NULL, present TINYINT(1) NOT NULL, arrival_time TIME NOT NULL, departure_time TIME NOT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_E370BA0154B1EB84 (semainier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, color_code VARCHAR(7) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE icon (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE journal (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, children_id INT NOT NULL, date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', meal VARCHAR(255) DEFAULT NULL, nap TIME DEFAULT NULL, diaper_time TIME DEFAULT NULL, diaper_type VARCHAR(50) DEFAULT NULL, activity LONGTEXT DEFAULT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_C1A7E74DDD62C21B (child_id), INDEX IDX_C1A7E74D3D3D2749 (children_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE planned_presence (id INT AUTO_INCREMENT NOT NULL, week_day DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', arrival_time TIME NOT NULL, departure_time TIME NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE semainier (id INT AUTO_INCREMENT NOT NULL, week_start_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_presence ADD CONSTRAINT FK_E370BA0154B1EB84 FOREIGN KEY (semainier_id) REFERENCES semainier (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74DDD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74D3D3D2749 FOREIGN KEY (children_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD icons_id INT DEFAULT NULL, ADD child_group_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD CONSTRAINT FK_22B354292FF25572 FOREIGN KEY (icons_id) REFERENCES icon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD CONSTRAINT FK_22B354297453A4E3 FOREIGN KEY (child_group_id) REFERENCES `group` (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_22B354292FF25572 ON child (icons_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_22B354297453A4E3 ON child (child_group_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP FOREIGN KEY FK_22B354297453A4E3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP FOREIGN KEY FK_22B354292FF25572
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_presence DROP FOREIGN KEY FK_E370BA0154B1EB84
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal DROP FOREIGN KEY FK_C1A7E74DDD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal DROP FOREIGN KEY FK_C1A7E74D3D3D2749
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE child_presence
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `group`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE icon
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE journal
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE planned_presence
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE semainier
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_22B354292FF25572 ON child
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_22B354297453A4E3 ON child
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP icons_id, DROP child_group_id
        SQL);
    }
}
