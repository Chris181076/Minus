<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527145009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE journal DROP FOREIGN KEY FK_C1A7E74D3D3D2749
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C1A7E74D3D3D2749 ON journal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal DROP children_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE journal ADD children_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal ADD CONSTRAINT FK_C1A7E74D3D3D2749 FOREIGN KEY (children_id) REFERENCES child (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C1A7E74D3D3D2749 ON journal (children_id)
        SQL);
    }
}
