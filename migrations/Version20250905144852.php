<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250905144852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_token DROP FOREIGN KEY FK_452C9EC5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_token
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande CHANGE adresse_postale adresse_postale VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE adresse_livraison adresse_livraison VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE couleurs couleurs JSON DEFAULT NULL, CHANGE tailles tailles JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE first_name first_name VARCHAR(50) NOT NULL, CHANGE last_name last_name VARCHAR(50) NOT NULL, CHANGE username username VARCHAR(50) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE nom nom VARCHAR(50) DEFAULT NULL, CHANGE adresse_postale adresse_postale VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL, CHANGE adresse_livraison adresse_livraison VARCHAR(255) DEFAULT NULL
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
            ALTER TABLE commande CHANGE adresse_postale adresse_postale VARCHAR(255) DEFAULT 'NULL', CHANGE telephone telephone VARCHAR(20) DEFAULT 'NULL', CHANGE adresse_livraison adresse_livraison VARCHAR(255) DEFAULT 'NULL'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE produit CHANGE couleurs couleurs LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE tailles tailles LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL, CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL, CHANGE username username VARCHAR(255) DEFAULT 'NULL', CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE adresse_postale adresse_postale VARCHAR(255) DEFAULT 'NULL', CHANGE telephone telephone VARCHAR(20) DEFAULT 'NULL', CHANGE adresse_livraison adresse_livraison VARCHAR(255) DEFAULT 'NULL'
        SQL);
    }
}
