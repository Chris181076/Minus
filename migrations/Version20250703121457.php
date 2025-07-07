<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703121457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

   
        // this up() migration is auto-generated, please modify it to your needs
public function up(Schema $schema): void
{
    // Supprimer la contrainte de clé étrangère
    $this->addSql('ALTER TABLE planned_presence DROP FOREIGN KEY FK_48831BF7DD62C21B');

    // Modifier les colonnes
    $this->addSql('ALTER TABLE planned_presence 
        CHANGE child_id child_id INT NOT NULL, 
        CHANGE arrival_time arrival_time TIME DEFAULT NULL, 
        CHANGE departure_time departure_time TIME DEFAULT NULL
    ');

    // Recréer la clé étrangère (ajoute ON DELETE CASCADE si souhaité)
    $this->addSql('ALTER TABLE planned_presence 
        ADD CONSTRAINT FK_48831BF7DD62C21B FOREIGN KEY (child_id) REFERENCES child (id) ON DELETE CASCADE
    ');
}

    

 public function down(Schema $schema): void
{
    // Supprimer la FK avec cascade
    $this->addSql('ALTER TABLE planned_presence DROP FOREIGN KEY FK_48831BF7DD62C21B');

    // Revenir aux anciens paramètres
    $this->addSql('ALTER TABLE planned_presence 
        CHANGE child_id child_id INT DEFAULT NULL, 
        CHANGE arrival_time arrival_time TIME NOT NULL, 
        CHANGE departure_time departure_time TIME NOT NULL
    ');

    // Recréer la FK sans cascade
    $this->addSql('ALTER TABLE planned_presence 
        ADD CONSTRAINT FK_48831BF7DD62C21B FOREIGN KEY (child_id) REFERENCES child (id)
    ');
}

}
