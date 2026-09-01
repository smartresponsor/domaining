<?php

declare(strict_types=1);

namespace App\Domaining\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260823192000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link domain claims to optional domain declarations without changing runtime activation semantics';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Domaining production schema requires PostgreSQL.');

        $claim = $schema->getTable('domain_claim');
        $claim->addColumn('declaration_id', 'uuid', ['notnull' => false]);
        $claim->addIndex(['declaration_id'], 'idx_domain_claim_declaration');
        $claim->addForeignKeyConstraint('domain_declaration', ['declaration_id'], ['id'], ['onDelete' => 'SET NULL'], 'fk_domain_claim_declaration');

        $binding = $schema->getTable('domain_binding');
        $binding->addColumn('declaration_id', 'uuid', ['notnull' => false]);
        $binding->addIndex(['declaration_id'], 'idx_domain_binding_declaration');
        $binding->addForeignKeyConstraint('domain_declaration', ['declaration_id'], ['id'], ['onDelete' => 'SET NULL'], 'fk_domain_binding_declaration');
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('domain_binding')->dropColumn('declaration_id');
        $schema->getTable('domain_claim')->dropColumn('declaration_id');
    }
}
