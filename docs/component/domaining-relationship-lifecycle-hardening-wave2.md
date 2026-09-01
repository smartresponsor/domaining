# Domaining relationship/lifecycle hardening wave 2

Status: applied as a conservative hardening pass.

## Scope

- Adds `DomainBindingLifecyclePolicy`.
- Keeps lifecycle validation string-based to avoid schema drift.
- Does not touch `*EnGb*` / translation normalization.
- Does not touch Attachment/Attaching mechanics.

## Lifecycle decision

Domain claim, verification, binding and publication lifecycle. DNS/publication timestamps stay local domain facts.

## Transition map

- `claimed` -> `verification_pending`, `withdrawn`
- `verification_pending` -> `verified`, `failed`, `withdrawn`
- `failed` -> `verification_pending`, `withdrawn`
- `verified` -> `bound`, `withdrawn`
- `bound` -> `published`, `suspended`, `removed`
- `published` -> `suspended`, `removed`
- `suspended` -> `published`, `removed`
- `removed` -> `terminal`
- `withdrawn` -> `terminal`
