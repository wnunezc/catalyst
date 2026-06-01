# Deployment Guide

This document defines the minimum security checks for packaging and deploying Catalyst without leaking local runtime data.

## Security Before Deployment

Apply this checklist before every deployment or archive export:

- Keep `boot-core/config/env/.env` local only. Distribute `boot-core/config/env/.env.example` and `boot-core/config/env/.env.ver`, never the real `.env`.
- Keep any additional real `boot-core/config/env/.env.*` variants local-only as well. Only `.env.example` and `.env.ver` are safe templates.
- Exclude `boot-core/config/dkim/` from commits and archives. DKIM private keys must stay outside distributable artifacts.
- Exclude runtime storage from archives:
  - `boot-core/storage/logs/`
  - `boot-core/storage/throttle/`
  - `boot-core/storage/*.pid`
  - `boot-core/storage/*.stamp`
- Exclude test uploads from archives:
  - `public/uploads/devtools/`
- Review the deployed `.env` before go-live:
  - `APP_ENV` must not stay in `development`
  - `APP_DISPLAY_LOGS` must be `false`
  - secrets must be replaced with production values
- Re-check `boot-core/config/{env}/devtools.json`, `security.json`, `session.json`, `cors.json`, and `websocket.json` against the target environment.
- Confirm DevTools stays blocked outside development and requires authenticated authorized access in development.

## Secret Rotation Checklist

If a real `.env`, a DKIM private key, or runtime logs leave the workstation or get committed/shared by mistake, treat the values as exposed and rotate:

- `APP_KEY`
- `DB_PASSWORD`
- `MAIL_PASSWORD`
- `FTP_PASSWORD`
- OAuth client secrets, if configured
- DKIM key pair and related DNS record
- Any session or remember-me tokens that depend on compromised secrets

Do not paste exposed values into tickets, docs, or chat transcripts.

## Packaging And Distribution

Do not create a distributable ZIP directly from the project root without first staging a clean export.

Minimum exclusion set for a release archive:

- `boot-core/config/env/.env`
- `boot-core/config/env/.env.*`
- `boot-core/config/dkim/`
- `boot-core/storage/logs/`
- `boot-core/storage/throttle/`
- `boot-core/storage/*.pid`
- `boot-core/storage/*.stamp`
- `public/uploads/devtools/`
- local IDE files and ad-hoc archives such as `*.zip`

Recommended flow:

1. Copy the project to a temporary staging directory.
2. Remove the excluded paths from the staging copy.
3. Verify `boot-core/config/env/` contains only safe template files.
4. Verify the staging copy does not contain runtime logs, throttle state, PID/stamp files, test uploads, or DKIM keys.
5. Create the archive from the staging copy only.

## Current Audit Note

Audit performed on 2026-05-14 found that an ad-hoc local archive had included sensitive runtime data, including the real `.env`, DKIM key material, logs, throttle state, PID/stamp files, and DevTools uploads.

That archive is no longer present in the project tree. Per explicit user clarification in the same stream, it did not become a public exposure event and is not being treated as a rotation incident.
