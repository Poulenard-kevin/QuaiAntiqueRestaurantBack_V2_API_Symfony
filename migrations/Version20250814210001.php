<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250814210001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE restaurant CHANGE description description LONGTEXT DEFAULT NULL, CHANGE max_guest max_guest INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(32) DEFAULT NULL, CHANGE last_name last_name VARCHAR(64) DEFAULT NULL, CHANGE guest_number guest_number SMALLINT DEFAULT NULL, CHANGE allergy allergy VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE first_name first_name VARCHAR(32) NOT NULL, CHANGE last_name last_name VARCHAR(64) NOT NULL, CHANGE guest_number guest_number SMALLINT NOT NULL, CHANGE allergy allergy VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE restaurant CHANGE description description LONGTEXT NOT NULL, CHANGE max_guest max_guest INT NOT NULL');
    }
}
