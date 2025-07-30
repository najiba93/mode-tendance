<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250724134713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE image_produit (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_BCB5BBFBF347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image_produit ADD CONSTRAINT FK_BCB5BBFBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_29A5EC27BCF5E72D ON produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP categorie_id, DROP image, DROP couleurs, DROP tailles, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE prix prix NUMERIC(10, 2) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE image_produit DROP FOREIGN KEY FK_BCB5BBFBF347EFB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image_produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD categorie_id INT NOT NULL, ADD image VARCHAR(255) NOT NULL, ADD couleurs JSON DEFAULT NULL, ADD tailles JSON DEFAULT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)
        SQL);
    }
}
