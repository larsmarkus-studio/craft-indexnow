# Changelog

## 0.2.0 - 2026-10-05

- The key route only matches the configured key, so other `*.txt` templates and routes no longer 404
- Deleting an entry that was never live no longer submits its URL
- Nested (Matrix) entries are skipped, in saves and in `submit/all`
- Sites without an absolute base URL are skipped instead of producing failing jobs

## 0.1.0 - 2026-10-05

First release.

- Queue job per site on entry save and delete, with the URLs fixed at queue time
- Key file served at `/{key}.txt`
- `lms-indexnow/submit/all` for the first run
- `key`, `enabled`, `sections` and `endpoint` settings
