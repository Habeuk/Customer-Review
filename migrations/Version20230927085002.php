<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230927085002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carousel (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, UNIQUE INDEX UNIQ_1DD747004D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE carousel ADD CONSTRAINT FK_1DD747004D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('ALTER TABLE review ADD carousel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6C1CE5B98 FOREIGN KEY (carousel_id) REFERENCES carousel (id)');
        $this->addSql('CREATE INDEX IDX_794381C6C1CE5B98 ON review (carousel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6C1CE5B98');
        $this->addSql('ALTER TABLE carousel DROP FOREIGN KEY FK_1DD747004D16C4DD');
        $this->addSql('DROP TABLE carousel');
        $this->addSql('DROP INDEX IDX_794381C6C1CE5B98 ON review');
        $this->addSql('ALTER TABLE review DROP carousel_id');
    }
}
