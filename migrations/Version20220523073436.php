<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220523073436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE b2_titre ADD extraction_id INT DEFAULT NULL, ADD extraction_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE b2_titre ADD CONSTRAINT FK_C9DC65FF992488A FOREIGN KEY (extraction_id) REFERENCES b2_extractions (id)');
        $this->addSql('CREATE INDEX IDX_C9DC65FF992488A ON b2_titre (extraction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE b2_titre DROP FOREIGN KEY FK_C9DC65FF992488A');
        $this->addSql('DROP INDEX IDX_C9DC65FF992488A ON b2_titre');
        $this->addSql('ALTER TABLE b2_titre DROP extraction_id, DROP extraction_at');
    }
}
