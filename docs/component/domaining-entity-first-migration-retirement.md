# Domaining entity-first migration retirement

## Scope

This pass retires `Domaining/migration/**` as a schema-first source. The Doctrine entity model is the source of truth for the custom-domain lifecycle.

## Current entity coverage

The retired migration tables are covered by existing Domaining entities:

- `domain_claim` -> `DomainClaimEntity`
- `domain_verification_challenge` -> `DomainVerificationChallengeEntity`
- `domain_binding` -> `DomainBindingEntity`
- `domain_routing_target` -> `DomainRoutingTargetEntity`
- `domain_publication_state` -> `DomainPublicationStateEntity`
- `domain_audit_record` -> `DomainAuditRecordEntity`

## Migration metadata lifted into Doctrine attributes

- claim unique owner/surface domain constraint
- binding unique domain constraint and owner/surface indexes
- routing target one-to-one binding uniqueness
- publication state one-to-one binding uniqueness and status indexes
- verification challenge claim/retry/ready indexes
- audit record domain/action created indexes

## Objecting decision

No new Objecting traits were injected in this pass. Domaining currently stores lifecycle-specific timestamps (`activatedAt`, `suspendedAt`, `removedAt`, `lastVerifiedAt`, `readyAt`, `publishedAt`, `withdrawnAt`, `checkedAt`, retry counters). They are business/runtime state for domain verification/publication, not generic duplicated system fields.

## Legacy monolith

`Entity-src(6).zip` was checked. No old `Domain`/`Domaining` monolith with additional relations was present, so there were no legacy associations to restore.

## Retired files

- `Domaining/migration/**`
