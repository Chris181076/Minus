<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612121926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD icon_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child ADD CONSTRAINT FK_22B3542954B9D732 FOREIGN KEY (icon_id) REFERENCES icon (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_22B3542954B9D732 ON child (icon_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP FOREIGN KEY FK_22B3542954B9D732
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_22B3542954B9D732 ON child
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child DROP icon_id
        SQL);
    }
}
