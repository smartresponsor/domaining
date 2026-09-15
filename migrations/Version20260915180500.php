<?php

declare(strict_types=1);

namespace App\Domaining\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915180500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adopt canonical Objecting audit metadata for domain bindings and verification challenges.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE domain_binding ADD modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE domain_binding ADD created_by VARCHAR(190) DEFAULT NULL');
        $this->addSql('ALTER TABLE domain_binding ADD modified_by VARCHAR(190) DEFAULT NULL');
        $this->addSql('ALTER TABLE domain_verification_challenge ADD modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE domain_verification_challenge ADD created_by VARCHAR(190) DEFAULT NULL');
        $this->addSql('ALTER TABLE domain_verification_challenge ADD modified_by VARCHAR(190) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE domain_binding DROP modified_at');
        $this->addSql('ALTER TABLE domain_binding DROP created_by');
        $this->addSql('ALTER TABLE domain_binding DROP modified_by');
        $this->addSql('ALTER TABLE domain_verification_challenge DROP modified_at');
        $this->addSql('ALTER TABLE domain_verification_challenge DROP created_by');
        $this->addSql('ALTER TABLE domain_verification_challenge DROP modified_by');
    }
}
