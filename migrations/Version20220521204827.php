<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220521204827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE b2_titre (id INT AUTO_INCREMENT NOT NULL, uh_id INT DEFAULT NULL, reference INT NOT NULL, type VARCHAR(2) NOT NULL, classe VARCHAR(2) NOT NULL, iep INT NOT NULL, ipp INT NOT NULL, facture INT NOT NULL, enter_at DATE NOT NULL, exit_at DATE NOT NULL, montant INT NOT NULL, encaissement INT DEFAULT NULL, restantdu INT DEFAULT NULL, pec VARCHAR(20) DEFAULT NULL, lot INT NOT NULL, payeur INT NOT NULL, code_rejet VARCHAR(20) DEFAULT NULL, desc_rejet VARCHAR(255) DEFAULT NULL, cree_at DATE NOT NULL, rejet_at DATE NOT NULL, designation VARCHAR(50) NOT NULL, insee INT NOT NULL, rang INT NOT NULL, naissance_at DATE NOT NULL, contrat VARCHAR(20) DEFAULT NULL, naissance_hf VARCHAR(10) DEFAULT NULL, rprs TINYINT(1) DEFAULT NULL, extraction DATE NOT NULL, INDEX IDX_C9DC65F90825B6C (uh_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE b2_traitements (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, titre_id INT NOT NULL, observation_id INT NOT NULL, precisions LONGTEXT DEFAULT NULL, traite_at DATE DEFAULT NULL, INDEX IDX_406B51BBA76ED395 (user_id), INDEX IDX_406B51BBD54FAE5E (titre_id), INDEX IDX_406B51BB1409DD88 (observation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE b2_uh (id INT AUTO_INCREMENT NOT NULL, numero INT NOT NULL, designation VARCHAR(255) NOT NULL, antenne VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE b2_titre ADD CONSTRAINT FK_C9DC65F90825B6C FOREIGN KEY (uh_id) REFERENCES b2_uh (id)');
        $this->addSql('ALTER TABLE b2_traitements ADD CONSTRAINT FK_406B51BBA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE b2_traitements ADD CONSTRAINT FK_406B51BBD54FAE5E FOREIGN KEY (titre_id) REFERENCES b2_titre (id)');
        $this->addSql('ALTER TABLE b2_traitements ADD CONSTRAINT FK_406B51BB1409DD88 FOREIGN KEY (observation_id) REFERENCES b2_observations (id)');
        $this->addSql('DROP TABLE uh');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE b2_traitements DROP FOREIGN KEY FK_406B51BBD54FAE5E');
        $this->addSql('ALTER TABLE b2_titre DROP FOREIGN KEY FK_C9DC65F90825B6C');
        $this->addSql('CREATE TABLE uh (id INT AUTO_INCREMENT NOT NULL, numero INT NOT NULL, designation VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, antenne VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE b2_titre');
        $this->addSql('DROP TABLE b2_traitements');
        $this->addSql('DROP TABLE b2_uh');
    }
}
