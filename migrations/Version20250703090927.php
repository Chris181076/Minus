<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703090927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        /*$this->addSql(<<<'SQL'
            ALTER TABLE child_presence CHANGE child_id child_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planned_presence CHANGE child_id child_id INT NOT NULL, CHANGE arrival_time arrival_time TIME DEFAULT NULL, CHANGE departure_time departure_time TIME DEFAULT NULL
        SQL);*/
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        /*$this->addSql(<<<'SQL'
            ALTER TABLE child_presence CHANGE child_id child_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE planned_presence CHANGE child_id child_id INT DEFAULT NULL, CHANGE arrival_time arrival_time TIME NOT NULL, CHANGE departure_time departure_time TIME NOT NULL
        SQL);*/
    }
}
