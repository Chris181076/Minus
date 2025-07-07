<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703125220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        /*$this->addSql(<<<'SQL'
            ALTER TABLE child_presence CHANGE child_id child_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planned_presence DROP FOREIGN KEY FK_48831BF7DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planned_presence ADD CONSTRAINT FK_48831BF7DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);*/
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        /*$this->addSql(<<<'SQL'
            ALTER TABLE child_presence CHANGE child_id child_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planned_presence DROP FOREIGN KEY FK_48831BF7DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planned_presence ADD CONSTRAINT FK_48831BF7DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);*/
    }
}
