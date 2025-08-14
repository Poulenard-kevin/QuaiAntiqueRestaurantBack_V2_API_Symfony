<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804165124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('picture')) {
            return;
        }
        // ... ici tes addSql('CREATE TABLE picture ...');
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('picture')) {
            return;
        }
        // ... ici tes addSql('DROP TABLE picture');
    }
}
