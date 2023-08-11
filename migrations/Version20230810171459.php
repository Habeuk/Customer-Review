<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230810171459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4D16C4DD');
        $this->addSql('DROP INDEX UNIQ_D34A04AD918020D9 ON product');
        $this->addSql('DROP INDEX IDX_D34A04AD4D16C4DD ON product');
        $this->addSql('ALTER TABLE product DROP shop_id, DROP title');
        $this->addSql('ALTER TABLE review CHANGE description review LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE review_summary DROP total, DROP mean');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review_summary ADD total INT DEFAULT NULL, ADD mean INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD shop_id INT NOT NULL, ADD title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4D16C4DD FOREIGN KEY (shop_id) REFERENCES shop (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD918020D9 ON product (handle)');
        $this->addSql('CREATE INDEX IDX_D34A04AD4D16C4DD ON product (shop_id)');
        $this->addSql('ALTER TABLE review CHANGE review description LONGTEXT NOT NULL');
    }
}
