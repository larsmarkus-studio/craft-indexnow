# 0002 — Nested (Matrix) entries are not submitted

**Status:** accepted · 2026-10-05

**Decision:** Only entries in a section are submitted, on save and in `submit/all`. Nested entries are skipped.

**Why:** Nested entries rarely have their own URL; checking each one on every owner save costs a query per block for nothing.

**Rejected:** A `nestedEntries` setting now (no project needs it yet); skipping only entries without a URL (still a query per block).

**Revisit if:** A project gives Matrix entries their own URI format; then add a setting to opt in, off by default.
