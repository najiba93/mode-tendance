<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250801122433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, commande VARCHAR(255) NOT NULL, date DATETIME NOT NULL, total DOUBLE PRECISION NOT NULL, nom VARCHAR(255) NOT NULL, adresse_postale VARCHAR(255) DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, adresse_livraison VARCHAR(255) DEFAULT NULL, INDEX IDX_6EEAA67DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE image_produit (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_BCB5BBFBF347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image_produit ADD CONSTRAINT FK_BCB5BBFBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_token DROP FOREIGN KEY FK_452C9EC5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_token
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP image, CHANGE categorie_id categorie_id INT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE prix prix NUMERIC(10, 2) NOT NULL, CHANGE couleurs couleurs JSON DEFAULT NULL, CHANGE tailles tailles JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD nom VARCHAR(255) NOT NULL, ADD adresse_postale VARCHAR(255) DEFAULT NULL, ADD telephone VARCHAR(20) DEFAULT NULL, ADD adresse_livraison VARCHAR(255) DEFAULT NULL, CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_used TINYINT(1) NOT NULL, INDEX IDX_452C9EC5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_token ADD CONSTRAINT FK_452C9EC5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE image_produit DROP FOREIGN KEY FK_BCB5BBFBF347EFB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE image_produit
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD image VARCHAR(255) NOT NULL, CHANGE categorie_id categorie_id INT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL, CHANGE couleurs couleurs LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE tailles tailles LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP nom, DROP adresse_postale, DROP telephone, DROP adresse_livraison, CHANGE username username VARCHAR(255) DEFAULT 'NULL', CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`
        SQL);
    }
}
