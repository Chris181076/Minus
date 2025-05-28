<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527123027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE allergy (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE child (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, birth_date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', medical_notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE child_allergy (child_id INT NOT NULL, allergy_id INT NOT NULL, INDEX IDX_A9050AAEDD62C21B (child_id), INDEX IDX_A9050AAEDBFD579D (allergy_id), PRIMARY KEY(child_id, allergy_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE child_special_diet (child_id INT NOT NULL, special_diet_id INT NOT NULL, INDEX IDX_C3BFEDA3DD62C21B (child_id), INDEX IDX_C3BFEDA339ED665B (special_diet_id), PRIMARY KEY(child_id, special_diet_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE special_diet (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_child (user_id INT NOT NULL, child_id INT NOT NULL, INDEX IDX_C071AF71A76ED395 (user_id), INDEX IDX_C071AF71DD62C21B (child_id), PRIMARY KEY(user_id, child_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_allergy ADD CONSTRAINT FK_A9050AAEDD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_allergy ADD CONSTRAINT FK_A9050AAEDBFD579D FOREIGN KEY (allergy_id) REFERENCES allergy (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_special_diet ADD CONSTRAINT FK_C3BFEDA3DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_special_diet ADD CONSTRAINT FK_C3BFEDA339ED665B FOREIGN KEY (special_diet_id) REFERENCES special_diet (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child ADD CONSTRAINT FK_C071AF71A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child ADD CONSTRAINT FK_C071AF71DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE child_allergy DROP FOREIGN KEY FK_A9050AAEDD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_allergy DROP FOREIGN KEY FK_A9050AAEDBFD579D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_special_diet DROP FOREIGN KEY FK_C3BFEDA3DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE child_special_diet DROP FOREIGN KEY FK_C3BFEDA339ED665B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child DROP FOREIGN KEY FK_C071AF71A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_child DROP FOREIGN KEY FK_C071AF71DD62C21B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE allergy
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE child
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE child_allergy
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE child_special_diet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE special_diet
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_child
        SQL);
    }
}
