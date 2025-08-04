<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804094756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sortie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, date_heure_debut DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', duree INT DEFAULT NULL, date_limite_inscription DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', nb_inscription_max INT DEFAULT NULL, infos_sortie VARCHAR(2000) DEFAULT NULL, etat VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sortie');
    }
}
