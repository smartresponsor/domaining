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
        if (!$claim->hasColumn('declaration_id')) {
            $this->addSql('ALTER TABLE domain_claim ADD declaration_id UUID DEFAULT NULL');
        }
        if (!$claim->hasIndex('idx_domain_claim_declaration')) {
            $this->addSql('CREATE INDEX idx_domain_claim_declaration ON domain_claim (declaration_id)');
        }
        if (!$claim->hasForeignKey('fk_domain_claim_declaration')) {
            $this->addSql('ALTER TABLE domain_claim ADD CONSTRAINT fk_domain_claim_declaration FOREIGN KEY (declaration_id) REFERENCES domain_declaration (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        }

        $binding = $schema->getTable('domain_binding');
        if (!$binding->hasColumn('declaration_id')) {
            $this->addSql('ALTER TABLE domain_binding ADD declaration_id UUID DEFAULT NULL');
        }
        if (!$binding->hasIndex('idx_domain_binding_declaration')) {
            $this->addSql('CREATE INDEX idx_domain_binding_declaration ON domain_binding (declaration_id)');
        }
        if (!$binding->hasForeignKey('fk_domain_binding_declaration')) {
            $this->addSql('ALTER TABLE domain_binding ADD CONSTRAINT fk_domain_binding_declaration FOREIGN KEY (declaration_id) REFERENCES domain_declaration (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE domain_binding DROP COLUMN IF EXISTS declaration_id');
        $this->addSql('ALTER TABLE domain_claim DROP COLUMN IF EXISTS declaration_id');
    }
}
