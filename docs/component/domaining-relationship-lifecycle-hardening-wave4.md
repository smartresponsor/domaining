# Domaining relationship + lifecycle hardening — Wave 4

Added `DomainClaimLifecyclePolicy` as a framework-free lifecycle guard.

This pass does not touch deferred areas:

- `*EnGb*` / translation-normalization.
- `Attachment` / `Attaching` relationship model.

The policy is intentionally not wired into setters automatically in this wave;
component services/entities can adopt it without changing current Doctrine
mapping or introducing cross-component ORM dependencies.
