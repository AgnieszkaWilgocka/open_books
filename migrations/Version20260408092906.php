<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408092906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1619C27D7E3C61F9 ON rental (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D7E3C61F9');
        $this->addSql('DROP INDEX IDX_1619C27D7E3C61F9 ON rental');
        $this->addSql('ALTER TABLE rental DROP owner_id');
    }
}
