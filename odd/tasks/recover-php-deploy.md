# Feature: Recover and deploy the PHP+MySQL Gibson store

Branch: `feature/recover-php-deploy` (from `release1`, commit `1018771`)
Date opened: 2026-09-30

## Objective

Recover the abandoned PHP+MySQL e-commerce project (guitar store) so it runs again and can be
published as a portfolio piece, with a working database, without hardcoded secrets, and on a host
that actually supports PHP + MySQL.

## Problem / Why

- `release1` (2025-02-10) is the last and most complete version; `Dev` and `main` are dead ends.
  `main` is the old static-only version already published on Netlify.
- The MySQL database (`lenguajes`, XAMPP) was lost and there is no `.sql` dump in the repository.
  The schema had to be reconstructed from how the PHP code queries it.
- Vercel has no official PHP runtime and offers no MySQL: the full app cannot run there.
  Decision taken with the user: deploy the real app to a PHP-capable host (Docker-based).
- A Gmail application password is committed in a public repository
  (`server/send_email.php:17-18`) and must be revoked and removed from code.
- The repository carries 265 MB, of which 264 MB are 199 images (some PNGs of 5-7 MB).

## Scope

In scope:
- Recoverable MySQL schema and seed data as versioned SQL files under `database/`.
- Environment-based DB and SMTP configuration (no secrets in code).
- Docker packaging so the app can run locally and be deployed to Railway/Render.
- Image weight reduction without renaming files (the DB stores image paths).
- Deployment documentation.

Out of scope (explicitly not part of this feature):
- Rewriting the app in a modern stack.
- Fixing SQL injection, md5 password hashing, or other pre-existing security debt beyond the
  leaked credential.
- Any change to `Dev` or `main`.

## Constraints

- Do not modify the `release1` branch history; all work happens on `feature/recover-php-deploy`.
- Seed data must reference image paths that exist in the repository.
- `product_category` must be seeded as `electric` / `acustic` because the listings query those
  literal values (`php/ElectricGuitars.php:13`, `php/AcousticGuitars.php:11`).
- The four stored procedures used by the admin must exist or admin order detail and deletes fail.

## Authorized scope

Local repository work only. No remote execution, no deploy, no push, no PR without explicit user
authorization. Revoking the leaked Gmail password is a user action on their Google account.

## TDD / checks

- TDD mode: not enabled. The project has no test suite and no test runner configured; tests being
  absent does not enable TDD. Checks are functional, run against the app inside Docker.
- Applicable checks per task:
  - SQL: `docker compose up mysql` and load both SQL files without error; row counts per table.
  - App: `docker compose up` then HTTP checks on `/index.php`, both catalog pages, search, admin
    login, and one product detail page; confirm no PHP fatal errors in the container log.
  - Images: total size before/after; every seeded image path resolves to an existing file.
- Delivery strategy: `single-pr`. The forecast exceeds the ~400 authored changed lines heuristic
  (SQL, Docker, docs, config). This is a personal repository with no PR review workflow; if a PR is
  opened it needs a `size:exception` note or a split.

## Tasks

- [ ] T1 - Repository hygiene: `.gitignore`, feature document, working branch. (commit)
- [ ] T2 - Database recovery: `database/01-schema.sql` (10 tables + 4 stored procedures),
      `database/02-seed.sql` (body_style, admin, products with real image paths, products_details),
      `database/README.md`. Column names verified against the PHP source, not assumed. (commit)
- [ ] T3 - Secrets out of code: `config/` reads DB and SMTP settings from environment variables;
      `server/connection.php` and `server/send_email.php` consume it; `.env.example` documents the
      variables; the leaked Gmail password is removed from the source. (commit)
- [ ] T4 - Docker packaging: `Dockerfile` (Apache + PHP + mysqli), `docker-compose.yml` (app +
      MySQL, auto-loads the SQL), `.dockerignore`. (commit)
- [ ] T5 - Image optimization: reduce the 264 MB of images keeping every filename intact. (commit)
- [ ] T6 - Deployment guide: `DEPLOY.md` with host setup, environment variables, MySQL provisioning,
      and the post-deploy checklist. (commit)
- [ ] T7 - Functional verification: run the stack in Docker and exercise the app end to end; record
      observed results and any failure honestly. (commit)

## Acceptance criteria

- `docker compose up` starts the app and MySQL, and the SQL files load with no errors.
- The site loads: home, electric catalog, acoustic catalog, search, product detail, register/login.
- The admin panel logs in with the seeded admin and lists users, products, orders, and discounts.
- No hardcoded credential remains in the repository source.
- Every seeded `product_image*` path resolves to a file that exists in the repository.
- Deployment steps documented and reproducible.

## Progress log

- T1 opened the branch `feature/recover-php-deploy` from `release1`.

## Next step

T2 - reconstruct and version the database schema and seed.

## Verification evidence

Pending. Recorded per task as it is observed.
