# Domaining

Domaining is the Smart Responsor component that owns the custom-domain lifecycle for externally registered domains. It provides a provider-neutral engine to manage domain verification, DNS challenge enforcement, and routing intents.

This module is **not** a domain registrar, DNS host, or a reverse proxy. It verifies domain ownership and marks domains as ready for publication on the platform.

## Current Posture

### What the component already does
- Domain claim registration and tracking.
- Generates DNS challenge values (TXT, CNAME verification records).
- Performs DNS ownership check checks.
- Binds verified domains to tenant boundaries.
- Tracks publication readiness and routing intents.
- Retains verification lifecycle audit logs.

### What this repository does not claim yet
- TLS/SSL certificate issuance (handled at the gateway/proxy layer).
- Automatic DNS record configuration (requires the customer's DNS provider).

## Runtime Surface & Entrypoints

The Domaining package coordinates verification and checks:
- `src/Controller/` - Contains endpoints for claiming and checking domains.
- `src/Service/` - Verification runners, DNS query clients, and state transition guards.
- `src/Entity/` - Persistent models for domain registrations and verification statuses.
- `src/Command/` - Periodic cron commands for re-validating verified domains.

## Production Package Posture

Domaining is designed to be consumed by the Host as a normal versioned Composer package in production. Development may use a local Composer path repository with symlinks, but production must resolve `domaining/domain` from its VCS repository and apply Domaining-owned Doctrine migrations before serving domain-bound traffic.

## Local Setup

Install dependencies:
```bash
composer install
```

Run test suite:
```bash
vendor/bin/phpunit
```

## Local Composer Path Installation

To integrate Domaining in your Symfony project:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../Domaining",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "domaining/domain": "*@dev"
  }
}
```

## Documentation Map

- [Domaining Responsibility Overview](docs/architecture/domaining-responsibility.adoc)
- [Routing Intent Boundary](docs/architecture/routing-intent-boundary.adoc)
- [Runtime Publication Contract](docs/architecture/runtime-publication-contract.adoc)
- [State Transition Guard](docs/architecture/state-transition-guard.adoc)
- [Domaining Endpoint Index](docs/api/domaining-endpoint-index.adoc)
- [OpenAPI Schema](docs/api/domaining-openapi.adoc)
