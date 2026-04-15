<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260415100432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorite_category (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, notifications_enabled TINYINT NOT NULL, owner_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_AC1B01307E3C61F9 (owner_id), INDEX IDX_AC1B013012469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE favorite_category ADD CONSTRAINT FK_AC1B01307E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE favorite_category ADD CONSTRAINT FK_AC1B013012469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorite_category DROP FOREIGN KEY FK_AC1B01307E3C61F9');
        $this->addSql('ALTER TABLE favorite_category DROP FOREIGN KEY FK_AC1B013012469DE2');
        $this->addSql('DROP TABLE favorite_category');
    }
}
