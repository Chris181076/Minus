<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624125508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE journal DROP meal, DROP nap, DROP activity, DROP note, DROP description, DROP heures, DROP actions, DROP notes, DROP diaper_type
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE journal ADD meal VARCHAR(255) DEFAULT NULL, ADD nap TIME DEFAULT NULL, ADD activity LONGTEXT DEFAULT NULL, ADD note LONGTEXT DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD heures LONGTEXT DEFAULT NULL, ADD actions LONGTEXT DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD diaper_type VARCHAR(50) DEFAULT NULL
        SQL);
    }
}
