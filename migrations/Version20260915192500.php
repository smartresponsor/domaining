<?php

declare(strict_types=1);

namespace App\Domaining\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915192500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove the redundant DomainBindingEntity domain_name index already covered by the canonical unique index.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Domaining production schema requires PostgreSQL.');

        $this->addSql('DROP INDEX IF EXISTS domain_binding_name_idx');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Domaining production schema requires PostgreSQL.');

        $this->addSql('CREATE INDEX domain_binding_name_idx ON domain_binding (domain_name)');
    }
}
