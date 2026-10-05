# 0001 — Entries only, config file, no CP page

**Status:** accepted · 2026-10-05

**Decision:** Only entries trigger submissions, and settings live in `config/lms-indexnow.php` and `.env`. The key file is served by the plugin, not committed to `web/`.

**Why:** URLs come from entries in every project so far; one key per environment fits `.env`; no committed file means no per-project drift.

**Rejected:** Category/asset elements; a CP settings page; a key file in `web/`.

**Revisit if:** A project needs non-entry URLs, or a client wants to manage the key in the CP.
