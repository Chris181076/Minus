<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613075029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child_presence ADD child_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_presence ADD CONSTRAINT FK_E370BA01DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E370BA01DD62C21B ON child_presence (child_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child_presence DROP FOREIGN KEY FK_E370BA01DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E370BA01DD62C21B ON child_presence
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_presence DROP child_id
        SQL);
    }
}
