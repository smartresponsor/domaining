<?php

declare(strict_types=1);

namespace App\Domaining\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915191500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align DomainDeclaration Objecting columns and DomainClaim audit nullability with current ORM metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Domaining production schema requires PostgreSQL.');

        $this->addSql('ALTER TABLE domain_claim ALTER COLUMN modified_at DROP NOT NULL');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN object_uuid TO uuid');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN object_slug TO slug');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN object_created_at TO created_at');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN object_modified_at TO modified_at');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN object_created_by TO created_by');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN object_modified_by TO modified_by');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Domaining production schema requires PostgreSQL.');

        $this->addSql('UPDATE domain_claim SET modified_at = created_at WHERE modified_at IS NULL');
        $this->addSql('ALTER TABLE domain_claim ALTER COLUMN modified_at SET NOT NULL');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN uuid TO object_uuid');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN slug TO object_slug');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN created_at TO object_created_at');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN modified_at TO object_modified_at');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN created_by TO object_created_by');
        $this->addSql('ALTER TABLE domain_declaration RENAME COLUMN modified_by TO object_modified_by');
    }
}
