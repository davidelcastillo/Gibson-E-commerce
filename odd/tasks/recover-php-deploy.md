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

- [x] T1 - Repository hygiene: `.gitignore`, feature document, working branch. Commit `c115fef`.
- [x] T2 - Database recovery: `database/01-schema.sql`, `database/02-seed.sql`, `database/README.md`.
      Column names verified against the PHP source by an independent verifier and by three separate
      MySQL 8.4 loads. Commit `27bcff4`.
- [x] T3 - Secrets out of code: `config/env.php`, `config/database.php`, `server/connection.php`,
      `server/send_email.php`, `php/Register.php`, `server/complete_payment.php`, `.env.example`.
      The leaked credential is gone from every source file. Commit `8e64c0d`.
- [x] T4 - Docker packaging: `Dockerfile`, `docker-compose.yml`, `.dockerignore`. Commit `af9da10`.
- [x] T5 - Image optimization: done at BUILD TIME inside a multi-stage `Dockerfile`, not by
      committing recompressed files. Rationale: rewriting 264 MB of images in git would have added a
      second copy of every blob to the history of an already large repository, with no way back
      without a destructive rewrite. The build stage downscales to max 1600px and quantizes, keeping
      every filename and extension the database references. Result: 264 MB -> 44 MB in the image
      (-84%), 199 files in and 199 out, identical path set. Commit `af9da10` (with T4).
- [x] T6 - Deployment guide: `DEPLOY.md`.
- [x] T7 - Functional verification: see the evidence section below; the one unverified acceptance
      criterion (authenticated admin listing) was closed by the orchestrator with a real login test.

## Out of scope, but found and requiring a decision

The admin data pages are NOT protected by the login: `admin/users.php`, `admin/orders.php`,
`admin/products.php`, `admin/discount.php` and `admin/reports.php` render full data with no session
check (verified anonymously: `users.php` returns 200 and shows the customer's email). Only
`admin/dashboard.php` and the five `admin/delete_*.php` scripts check `$_SESSION['admin_logged_in']`.
With the seeded throwaway data the real impact is low, but any real customer data placed there would
be publicly readable. Fixing it is a behavior change to the app and therefore NEW scope: it needs
explicit authorization. Candidate task T8: add the same guard used by `dashboard.php` to the five
data pages.

## Acceptance criteria

- [x] `docker compose up` starts the app and MySQL, and the SQL files load with no errors.
- [x] The site loads: home, electric catalog, acoustic catalog, search, product detail.
- [x] The admin panel logs in with the seeded admin and lists users, products, orders and discounts.
      (`dashboard.php` returns 200 with a real authenticated session; `users.php` shows the seeded
      customer row. Note the guard gap above: the listing pages do not actually require that session.)
- [x] No hardcoded credential remains in the repository source. Zero occurrences of the value and of
      the real address across 190 scanned text files.
- [x] Every seeded image path resolves to a file that exists in the repository (177 paths checked,
      0 missing).
- [x] Deployment steps documented and reproducible.

## Progress log

- T1 opened the branch `feature/recover-php-deploy` from `release1`.
- T2 recovered the schema from the PHP source and had it independently verified column by column.
- T3 moved DB and SMTP settings to the environment; the credential turned up in three files, not one.
- T4/T5 packaged the app and moved image optimization into the build stage.
- T6 documented the deployment, including provider claims verified against official docs.
- T7 exercised the running stack and closed the admin-login gap in the evidence.

## Verification evidence (observed, not assumed)

Database load — three independent runs, each loading both SQL files into MySQL 8.4:
- 10 tables, 4 stored procedures (`GetOrderDetails`, `mov_n_delete_order`,
  `mov_n_delete_product`, `mov_n_delete_user`), 25 products, `electric=18`, `acustic=7`, 1 admin.
- `CALL GetOrderDetails(1)` returns the six aliases `admin/order_details.php` reads.
- Re-running the seed leaves the counts unchanged (idempotent).
- The FK-safe delete procedures remove children before parents without error.
- Independent column audit against the PHP source: 0 missing columns, 0 invented columns, for all 10
  tables. `GetOrderDetails` aliases match 6/6.
- 177 seeded image paths checked against disk: 0 missing.

Application — the compose stack, verified with HTTP requests:
- `/index.php`, `/php/ElectricGuitars.php` (8 product cards), `/php/AcousticGuitars.php` (7 cards),
  `/php/ProductDestail.php?product_id=1`, `/php/Search.php` (POST "Les Paul" matched a product),
  `/admin/login.php`: all HTTP 200.
- `docker compose logs web`: no PHP fatal, warning, notice or parse error.
- Admin login: a session that POSTs the seeded credentials is then able to GET the guarded
  `admin/dashboard.php` without being redirected — the login genuinely works.
- Images: `/img/guitarras_lp/lp_standar60.png` served at 211,171 bytes from a 1,199,810 byte source.
- PHP lint over the whole app: 78-80 files, zero syntax errors.

Limitations recorded honestly:
- Email delivery was never exercised: no SMTP credentials exist, so only the configuration path was
  checked (the missing-variable failure message was observed).
- The native immutable receipt review is unavailable in this runtime: `gentle-ai review assess`
  returned `unassessable` because only claude-code and codex are eligible. Indexed review was not
  performed; verification rests on the two delegated writers' observed command output plus the
  independent verifier and the orchestrator's own HTTP checks.
- The repository working tree still holds the 264 MB of source images. The optimization is applied
  in the image, so the disk weight and the git history are unchanged.

## Next step

Owner decisions, in order:
1. Revoke the Gmail application password in the Google account: it is still in the git history of a
   public repository, and removing it from the source does not revoke it.
2. Decide on the admin guard gap (candidate task T8) described above.
3. Pick the deploy host and follow `DEPLOY.md`; the app is already running locally on
   `http://localhost:8080` via `docker compose up -d --build`.
