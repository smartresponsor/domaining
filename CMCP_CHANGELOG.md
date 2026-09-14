# CMCP Orchestration Journal

## engine-20260911143859-domaining-a3a00d

### Iteration 1 — reconnaissance and baseline

- Workspace: `D:\PhpstormProjects\www\Domaining`.
- Branch/HEAD: `feature/application-runtime-overlay-20260901` at `b623ff3103f0c1ec3d5f70c6003600b186475cc6`.
- Pre-existing worktree state: untracked `.gating/`; treat as pre-existing and do not overwrite or stage without a task-specific reason.
- Repository role: standalone-capable Symfony bundle/application owning externally registered custom-domain declaration, ownership verification, binding, publication readiness, routing intent, and audit. Registrar, DNS-hosting, TLS issuance, reverse-proxy mutation, and provider-specific runtime mutation remain outside Domaining.
- Runtime dependency baseline is declared directly in Composer: `cruding/crud`, `viewing/view`, `interfacing/interface`, `objecting/object`, and `easycorp/easyadmin-bundle`; local path repositories exist for Cruding, Interfacing, Objecting, and Viewing.
- Current standalone bundle registration includes Cruding, Interfacing, Objecting, Viewing, EasyAdmin, Doctrine, Security, Twig, and Domaining.
- `MANIFEST.json` is stale relative to the current tree: current runtime-overlay/declaration files exist but are absent from the W16 manifest. README documentation links also contain stale `docs/domaining/*` paths while the manifest records `docs/api/*`.
- No component-local generic CRUD engine was found in the inspected current surface. `DomainDeclarationCrudController` is EasyAdmin-derived and therefore falls under the explicit Canon021 exception.

### Canonization mapping consulted

- `Canon019NoAlternativeLayerTaxonomyRule.md`: Domaining uses role-first roots and must not introduce `src/Domain`, `src/Application`, `src/Infrastructure`, `src/Port`, `src/Adapter`, or `src/Adaptor`. Current inspected source respects this root rule.
- `Canon021CrudingOwnsGenericCrudRule.md`: generic application CRUD belongs to Cruding. Current EasyAdmin declaration CRUD is allowed; no parallel generic CRUD implementation should be added.
- `Canon022StandaloneApplicationDependencyBaselineRule.md`: standalone Symfony applications require direct runtime dependencies on Cruding, Viewing, Interfacing, Objecting, and EasyAdmin. Domaining currently declares this baseline.
- Objecting lifecycle canon: canonical audit vocabulary is `created` / `modified`; consumers should use Objecting packs and canonical methods rather than local timestamp fields or `updated*` aliases.

### RC-critical workstream selected

Normalize Domaining lifecycle/system-field usage against Objecting without crossing repository boundaries. Facts found during reconnaissance:

- `DomainDeclaration` already composes `ObjectIdentityEmbeddableTrait` and `ObjectAuditEmbeddableTrait`, but exposes a legacy `updatedAt()` alias and EasyAdmin binds an `updatedAt` field instead of the canonical modified surface.
- `DomainClaim`, `DomainBinding`, `DomainAuditRecord`, and `DomainVerificationChallenge` still contain local `createdAt` and/or `updatedAt` storage instead of the canonical Objecting lifecycle pack.
- Migration must be semantic and entity-by-entity; business timestamps must not be converted mechanically. Runtime callers, repositories, forms/admin fields, persistence mapping, and tests/gates must be updated together.

### Growth workstream (non-blocking for RC)

Continue maturity of the application runtime-overlay/declaration capability after RC correctness is green: add explicit consumer-facing contract tests/diagnostics, strengthen status projection semantics, and align release manifest/documentation with the current runtime overlay. Keep provider-specific TLS/proxy/DNS mutation outside Domaining.

### Risks and safeguards

- Do not alter sibling repositories; Objecting, Cruding, Viewing, Interfacing, Gating, and Canonization are reference-only for this task.
- Do not touch pre-existing `.gating/` unless a verified gate requires an in-scope local configuration change.
- Preserve existing data semantics when replacing local lifecycle fields; do not conflate business event timestamps with Objecting audit fields.

### Iteration 2 — material implementation

- Canonicalized `DomainDeclaration` against Objecting without changing persisted schema: it now explicitly implements `ObjectAuditedInterface` and `ObjectIdentifiedInterface` already backed by its Objecting traits.
- Removed legacy `createdAt()` / `updatedAt()` compatibility aliases from `DomainDeclaration`; canonical lifecycle access is now `getCreatedAt()` / `getModifiedAt()`.
- Updated EasyAdmin `DomainDeclarationCrudController` from legacy `updatedAt` to canonical `modifiedAt`; EasyAdmin remains within the Canon021 admin exception.
- PHP syntax lint passed for both changed product PHP files. The broad changed-file lint also traversed the pre-existing untracked `.gating/` tree; all reported files were syntactically valid, but `.gating/` remains outside this task's mutation/staging scope.
- `composer install --dry-run` passed: lock contents are installable and no dependency changes are required.
- `composer validate --strict` reports `composer.json` valid, but exits non-zero because existing sibling `*@dev` constraints are unbound. This is packaging debt, not a syntax/schema error introduced by this wave.
- Symfony `lint:container` passed and `lint:yaml config --parse-tags` passed for all 8 YAML files.
- `doctrine:schema:validate` is runtime-blocked because `DATABASE_URL` is not defined; no destructive database fallback was attempted.

Что имеем? The already-Objecting-backed declaration entity now exposes only canonical Objecting lifecycle contracts, with container/YAML/PHP validation green.

Что осталось? Iteration 3 must inspect the resulting state, run targeted legacy/canon checks, fix any in-scope regression, and classify the remaining local lifecycle fields before deciding whether a schema-changing migration wave is safe in this run.

### Iteration 3 — verification and fix

- Targeted search after Iteration 2 showed `updatedAt` only in `DomainClaim`; the declaration/admin legacy surface is fully removed.
- Verified the repository has an explicit Domaining migration namespace/path (`App\\Domaining\\Migration` -> `migration/`), so the remaining `DomainClaim.updated_at` can be migrated forward rather than silently changing mapping.
- `DomainClaim` now implements `ObjectAuditedInterface`, composes `ObjectAuditEmbeddableTrait`, preserves its existing creation timestamp through `initializeObjectAudit($now)`, and routes modifications through `touchModified()`.
- No lifecycle actor was inferred from `ownerId`: that field is business ownership context, and the current code does not prove it is the actor identity required by Objecting `created_by` / `modified_by`.
- A forward migration is required to rename `updated_at` to `modified_at` and add nullable `created_by` / `modified_by` columns while preserving `created_at` data.

Что имеем? The last concrete `updatedAt` implementation has been removed from product code without inventing identity semantics.

Что осталось? Add and verify the forward migration, then close non-schema documentation/manifest drift and Git integration in Iteration 4.

### Iteration 4 — debt closure and integration

- Added `migration/Version20260911205500.php`: preserves `domain_claim.created_at`, renames `updated_at` to canonical `modified_at`, and adds nullable `created_by` / `modified_by`; the down migration reverses the change.
- PHP lint passed for `DomainClaim.php` and the new migration.
- Targeted search confirms no `updatedAt` token remains in tracked Domaining source.
- Corrected stale README documentation links from `docs/domaining/*` to the existing `docs/api/*` paths.
- Corrected the static delivery manifest by removing six missing 2026-05 migration paths, adding the current migration, and registering the current consumer ensure command.
- `domaining:release:manifest` executes successfully and reports `releaseCandidateReady: true`.
- `domaining:contract:governance` executes successfully with `ready: true` and zero issues.
- `domaining:release:gate`, `doctrine:schema:validate`, and `doctrine:mapping:info` are blocked by the absent `DATABASE_URL`; this is an environment prerequisite, not an observed code failure. No destructive database workaround was used.
- Existing untracked `.gating/` remains untouched and will not be staged.

Что имеем? RC-critical declaration/claim lifecycle drift is normalized, its schema transition is explicit, provider-neutral release/contract checks are green, and documentation/migration manifest drift is reduced.

Что осталось? Create the coherent task commit, publish the feature branch if remote access is available, inspect integration state, then perform Iteration 5 final acceptance on the post-integration HEAD.

### Iteration 5 — final acceptance and handoff

- Coherent signed implementation commit created: `25a4486c706a0e39ad124c3b349d43baee67de1b` (`Normalize Domaining Objecting lifecycle`).
- Post-commit product tree is clean; the only remaining worktree entry is the pre-existing untracked `.gating/` directory intentionally excluded from this task.
- Remote `origin` is configured as `git@github.com:smartresponsor/domaining.git` and the current feature branch has no upstream yet.
- Console MCP push was attempted with explicit confirmation and blocked by its `working_tree_dirty` guard because of that pre-existing `.gating/`. No attempt was made to delete, stage, commit, move, or otherwise mutate the unrelated `.gating/` tree to bypass policy.
- Therefore PR creation/merge cannot be factually completed in this run because the committed branch cannot first be published through the authorized Console MCP Git path.
- Final code acceptance remains green for the gates that do not require database configuration: changed PHP lint, Symfony container lint, YAML lint, Composer install dry-run, runtime release manifest, contract governance, and zero tracked `updatedAt` occurrences.
- Database-dependent acceptance remains externally blocked by missing `DATABASE_URL`: Doctrine mapping/schema validation and the database-backed release gate were not runnable.

Что имеем? The bounded local Domaining task is materially implemented and committed; the canonical Objecting declaration/claim lifecycle surface is in place with an explicit forward migration and corrected repository documentation/manifest references.

Что осталось? Remote publication/PR/merge and database-backed gates require external prerequisites: either a clean worktree that resolves ownership of the pre-existing `.gating/`, and a configured `DATABASE_URL`. No additional safe in-scope source change is justified merely to consume budget.

## 2026-09-14 — Canon022/Canon043 RC package-contract pass

### Reconnaissance baseline

- Re-read Domaining repository instructions, manifest, package contract, architecture/API documentation, current source inventory, and Git state.
- Re-read mandatory Objecting, Cruding, Viewing, and Interfacing contracts plus Gating and Canonization governance.
- Consulted the normative Canon022, Canon043, and Canon044 rule documents directly; verified Collectioning and Tabling package identities from their local Composer manifests.
- Market/reference check confirmed the existing responsibility boundary: ownership validation/readiness belongs in Domaining, while certificate issuance, DNS hosting, and edge routing execution stay in provider/runtime infrastructure.
- Pre-existing untracked `.gating/` remains outside this run's ownership.

### Target-to-canon mapping and selected RC work

- Canon022: Domaining is standalone-capable and must directly require Cruding, Collectioning, Tabling, Viewing, Interfacing, Objecting, and EasyAdmin. Collectioning and Tabling were missing.
- Canon043: every first-party local path repository must pin the sibling package as `dev-master`, matching direct requirements; the root must use `minimum-stability=dev` and `prefer-stable=true`. Existing `*@dev` constraints and unversioned path entries were non-canonical.
- Canon044 remains applicable to active Doctrine mappings; no new field migration is justified without a concrete mapping finding.
- RC-critical implementation selected: normalize local package wiring and complete the direct baseline dependency contour. Growth work remains separate: richer pre-validation/DCV telemetry and zero-downtime provider migration semantics.

### Material implementation and risks

- Added direct Collectioning and Tabling dependencies and their local path repositories.
- Pinned Administering, Collectioning, Cruding, Interfacing, Objecting, Tabling, and Viewing path repositories and matching first-party constraints to `dev-master`.
- Added canonical development stability flags.
- Refreshed the dependency graph to the current canonical first-party `dev-master` contour; Collectioning and Tabling are now present and Cruding is no longer locked to a feature branch.
- Composer audit exposed high-severity CVE-2026-67434 in PHPCS 4.0.1; package-scoped update to 4.0.4 cleared the advisory and repeat audit is green.
- Canon022 runtime activation initially exposed a real custom-Kernel drift: `config/bundles.php` was not the active registration surface. `Kernel::registerBundles()` now registers Collectioning and Tabling as well; container and YAML lint are green.
- Added a focused PHPUnit harness and integration regression test for the standalone dependency baseline. PHPUnit passes with 1 test / 2 assertions using test-only in-memory SQLite configuration.
- Added reproducible Composer `test`, `phpstan`, and `qa` scripts. PHPStan level 6 initially found 23 issues; all were repaired without a baseline or analyzer weakening. The pass also fixed a real nonexistent `DomainName::fromString()` call, corrected release DTO shapes, repository/admin generics, lifecycle read accessors, and unreachable/nullsafe branches. Final PHPStan reports zero errors across 164 files.
- Standalone reproducibility was corrected by tracking `composer.lock`; `.console-mcp/` local runtime state is ignored. The generated application `config/reference.php` was refreshed by the updated installed dependency graph.
- Final green gates: `composer validate --strict --check-lock`, `composer audit`, full tracked PHP lint, Composer `qa`, Symfony `lint:container`, YAML lint, `domaining:contract:governance` (`ready: true`, zero issues), and `domaining:release:manifest` (`releaseCandidateReady: true`).
- Database-backed `domaining:release:gate` and `domaining:release:review` remain externally blocked because this workspace has no `DATABASE_URL`. No fake production-equivalent database was introduced to manufacture a green result.
- Gating was read as the executable canon companion and its package/CLI contract was inspected. The available guarded Console MCP command surface does not expose a cross-workspace PHP-binary invocation for `Gating/bin/gating --target=Domaining`; no permanent wrapper or invented Gating dependency was added merely to bypass that execution boundary.

Что имеем? Canon022/Canon043 package and runtime wiring is reproducible, security-clean, statically clean, regression-tested, and green across every available non-database RC gate.

Что осталось? Commit the coherent Domaining-owned changes and attempt publication of the current feature branch. Database-backed release acceptance still requires a real configured `DATABASE_URL`; pre-existing `.gating/` remains untouched and outside this run's ownership.
