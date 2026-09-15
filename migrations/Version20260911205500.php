<?php

declare(strict_types=1);

namespace App\Domaining\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260911205500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize DomainClaim audit fields to the canonical Objecting created/modified lifecycle columns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE domain_claim RENAME COLUMN updated_at TO modified_at');
        $this->addSql('ALTER TABLE domain_claim ADD created_by VARCHAR(190) DEFAULT NULL');
        $this->addSql('ALTER TABLE domain_claim ADD modified_by VARCHAR(190) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE domain_claim DROP created_by');
        $this->addSql('ALTER TABLE domain_claim DROP modified_by');
        $this->addSql('ALTER TABLE domain_claim RENAME COLUMN modified_at TO updated_at');
    }
}
