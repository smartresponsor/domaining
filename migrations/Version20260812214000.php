<?php

declare(strict_types=1);

namespace App\Domaining\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260812214000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Materialize Domaining lifecycle entities, including Objecting fields used by domain declarations';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Domaining production schema requires PostgreSQL.');

        $declaration = $schema->createTable('domain_declaration');
        $declaration->addColumn('id', 'uuid');
        $declaration->addColumn('application_key', 'string', ['length' => 128]);
        $declaration->addColumn('brand_key', 'string', ['length' => 128]);
        $declaration->addColumn('environment', 'string', ['length' => 32]);
        $declaration->addColumn('domain_name', 'string', ['length' => 253]);
        $declaration->addColumn('role', 'string', ['length' => 32]);
        $declaration->addColumn('status', 'string', ['length' => 32]);
        $declaration->addColumn('object_uuid', 'binary', ['length' => 16, 'fixed' => true]);
        $declaration->addColumn('object_slug', 'string', ['length' => 190]);
        $declaration->addColumn('object_created_at', 'datetime_immutable');
        $declaration->addColumn('object_modified_at', 'datetime_immutable', ['notnull' => false]);
        $declaration->addColumn('object_created_by', 'string', ['length' => 190, 'notnull' => false]);
        $declaration->addColumn('object_modified_by', 'string', ['length' => 190, 'notnull' => false]);
        $declaration->setPrimaryKey(['id']);
        $declaration->addUniqueIndex(['domain_name', 'environment'], 'domain_declaration_name_environment_unique');
        $declaration->addUniqueIndex(['object_uuid'], 'uniq_domain_declaration_object_uuid');
        $declaration->addUniqueIndex(['object_slug'], 'uniq_domain_declaration_object_slug');
        $declaration->addIndex(['application_key', 'environment'], 'domain_declaration_application_environment_idx');
        $declaration->addIndex(['status'], 'domain_declaration_status_idx');

        $claim = $schema->createTable('domain_claim');
        $claim->addColumn('id', 'uuid');
        $claim->addColumn('domain_name', 'string', ['length' => 253]);
        $claim->addColumn('owner_id', 'string', ['length' => 128]);
        $claim->addColumn('surface_type', 'string', ['length' => 32]);
        $claim->addColumn('surface_key', 'string', ['length' => 128]);
        $claim->addColumn('status', 'string', ['length' => 32]);
        $claim->addColumn('created_at', 'datetime_immutable');
        $claim->addColumn('updated_at', 'datetime_immutable');
        $claim->setPrimaryKey(['id']);
        $claim->addUniqueIndex(['domain_name', 'owner_id', 'surface_type', 'surface_key'], 'domain_claim_name_owner_surface_unique');

        $challenge = $schema->createTable('domain_verification_challenge');
        $challenge->addColumn('id', 'uuid');
        $challenge->addColumn('claim_id', 'uuid');
        $challenge->addColumn('record_type', 'string', ['length' => 16]);
        $challenge->addColumn('record_name', 'string', ['length' => 253]);
        $challenge->addColumn('record_value', 'string', ['length' => 512]);
        $challenge->addColumn('status', 'string', ['length' => 32]);
        $challenge->addColumn('expires_at', 'datetime_immutable');
        $challenge->addColumn('created_at', 'datetime_immutable');
        $challenge->addColumn('verified_at', 'datetime_immutable', ['notnull' => false]);
        $challenge->addColumn('checked_at', 'datetime_immutable', ['notnull' => false]);
        $challenge->addColumn('next_check_after', 'datetime_immutable', ['notnull' => false]);
        $challenge->addColumn('attempt_count', 'integer');
        $challenge->addColumn('last_failure_reason', 'string', ['length' => 512, 'notnull' => false]);
        $challenge->setPrimaryKey(['id']);
        $challenge->addIndex(['claim_id'], 'idx_domain_verification_challenge_claim');
        $challenge->addIndex(['status', 'expires_at', 'next_check_after'], 'domain_verification_ready_idx');
        $challenge->addIndex(['status', 'next_check_after'], 'domain_verification_challenge_status_retry_idx');
        $challenge->addForeignKeyConstraint('domain_claim', ['claim_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_domain_verification_claim');

        $binding = $schema->createTable('domain_binding');
        $binding->addColumn('id', 'uuid');
        $binding->addColumn('domain_name', 'string', ['length' => 253]);
        $binding->addColumn('owner_id', 'string', ['length' => 128]);
        $binding->addColumn('surface_type', 'string', ['length' => 32]);
        $binding->addColumn('surface_key', 'string', ['length' => 128]);
        $binding->addColumn('status', 'string', ['length' => 32]);
        $binding->addColumn('created_at', 'datetime_immutable');
        $binding->addColumn('activated_at', 'datetime_immutable', ['notnull' => false]);
        $binding->addColumn('suspended_at', 'datetime_immutable', ['notnull' => false]);
        $binding->addColumn('removed_at', 'datetime_immutable', ['notnull' => false]);
        $binding->addColumn('last_verified_at', 'datetime_immutable', ['notnull' => false]);
        $binding->setPrimaryKey(['id']);
        $binding->addUniqueIndex(['domain_name'], 'domain_binding_name_active_unique');
        $binding->addIndex(['domain_name'], 'domain_binding_name_idx');
        $binding->addIndex(['owner_id', 'surface_type', 'surface_key'], 'domain_binding_owner_surface_idx');

        $target = $schema->createTable('domain_routing_target');
        $target->addColumn('id', 'uuid');
        $target->addColumn('binding_id', 'uuid');
        $target->addColumn('target_host', 'string', ['length' => 253]);
        $target->addColumn('target_path', 'string', ['length' => 512]);
        $target->setPrimaryKey(['id']);
        $target->addUniqueIndex(['binding_id'], 'uniq_domain_routing_target_binding');
        $target->addForeignKeyConstraint('domain_binding', ['binding_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_domain_routing_target_binding');

        $publication = $schema->createTable('domain_publication_state');
        $publication->addColumn('id', 'uuid');
        $publication->addColumn('binding_id', 'uuid');
        $publication->addColumn('status', 'string', ['length' => 32]);
        $publication->addColumn('ready_at', 'datetime_immutable', ['notnull' => false]);
        $publication->addColumn('published_at', 'datetime_immutable', ['notnull' => false]);
        $publication->addColumn('withdrawn_at', 'datetime_immutable', ['notnull' => false]);
        $publication->setPrimaryKey(['id']);
        $publication->addUniqueIndex(['binding_id'], 'uniq_domain_publication_state_binding');
        $publication->addIndex(['status'], 'domain_publication_state_status_idx');
        $publication->addIndex(['status', 'ready_at'], 'domain_publication_state_status_updated_idx');
        $publication->addForeignKeyConstraint('domain_binding', ['binding_id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_domain_publication_state_binding');

        $audit = $schema->createTable('domain_audit_record');
        $audit->addColumn('id', 'uuid');
        $audit->addColumn('domain_name', 'string', ['length' => 253]);
        $audit->addColumn('action', 'string', ['length' => 96]);
        $audit->addColumn('actor_id', 'string', ['length' => 128, 'notnull' => false]);
        $audit->addColumn('context', 'json');
        $audit->addColumn('created_at', 'datetime_immutable');
        $audit->setPrimaryKey(['id']);
        $audit->addIndex(['domain_name', 'created_at'], 'domain_audit_record_domain_created_idx');
        $audit->addIndex(['action', 'created_at'], 'domain_audit_record_action_created_idx');
    }

    public function down(Schema $schema): void
    {
        foreach (['domain_publication_state', 'domain_routing_target', 'domain_verification_challenge', 'domain_binding', 'domain_claim', 'domain_audit_record', 'domain_declaration'] as $table) {
            if ($schema->hasTable($table)) {
                $schema->dropTable($table);
            }
        }
    }
}
