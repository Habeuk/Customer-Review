<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230727083701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, handle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, handle_id INT NOT NULL, title VARCHAR(255) NOT NULL, review LONGTEXT NOT NULL, INDEX IDX_794381C69C256C9C (handle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review_summary (id INT AUTO_INCREMENT NOT NULL, handle_id INT NOT NULL, note1 INT NOT NULL, note2 INT NOT NULL, note3 INT NOT NULL, note4 INT NOT NULL, note5 INT NOT NULL, UNIQUE INDEX UNIQ_55C6D2949C256C9C (handle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C69C256C9C FOREIGN KEY (handle_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE review_summary ADD CONSTRAINT FK_55C6D2949C256C9C FOREIGN KEY (handle_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C69C256C9C');
        $this->addSql('ALTER TABLE review_summary DROP FOREIGN KEY FK_55C6D2949C256C9C');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE review_summary');
    }
}
