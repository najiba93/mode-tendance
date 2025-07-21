<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721132328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE produit ADD tailles JSON DEFAULT NULL, CHANGE couleurs couleurs JSON DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE username username VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE produit DROP tailles, CHANGE couleurs couleurs LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE username username VARCHAR(255) DEFAULT 'NULL', CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`
        SQL);
    }
}
