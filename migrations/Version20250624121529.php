<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624121529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE journal_entry (id INT AUTO_INCREMENT NOT NULL, journal_id INT NOT NULL, heure TIME NOT NULL COMMENT '(DC2Type:time_immutable)', action VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_C8FAAE5A478E8802 (journal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE journal_entry ADD CONSTRAINT FK_C8FAAE5A478E8802 FOREIGN KEY (journal_id) REFERENCES journal (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE journal_entry DROP FOREIGN KEY FK_C8FAAE5A478E8802
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE journal_entry
        SQL);
    }
}
