<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416100632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book_queue (id INT AUTO_INCREMENT NOT NULL, missing_opportunity INT NOT NULL, user_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_713F6A1AA76ED395 (user_id), INDEX IDX_713F6A1A16A2B381 (book_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE book_queue ADD CONSTRAINT FK_713F6A1AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE book_queue ADD CONSTRAINT FK_713F6A1A16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_queue DROP FOREIGN KEY FK_713F6A1AA76ED395');
        $this->addSql('ALTER TABLE book_queue DROP FOREIGN KEY FK_713F6A1A16A2B381');
        $this->addSql('DROP TABLE book_queue');
    }
}
