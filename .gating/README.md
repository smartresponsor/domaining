# Gating consumer artifact surface

Domaining consumes the shared `gating/gate` package through Composer.

This local `.gating/` directory is reserved for generated artifact state only:
reports, evidence, cache data, checksums, and generated artifacts. Normative
configuration, executable policy, rule implementations, and the Gating engine
remain owned by the sibling `Gating` repository and canonical Symfony
configuration surfaces.
