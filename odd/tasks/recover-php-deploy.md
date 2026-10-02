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
- [x] T7 - Functional verification: see the evidence section below.
- [x] T8 - Admin authorization (authorized by the owner after the finding). `admin/require_login.php`
      is required by the 25 admin pages, the report pages and the three admin JSON endpoints. Commit
      `8d24e31`. This task also fixed the root cause behind the broken redirects (see below).
- [x] T9 - Linux case-sensitivity fixes. Commit `fc038ab`.

## T8 - what was actually wrong, and the root cause

The original finding was "five data pages render without a session". Measuring the whole surface
showed the hole was larger: **25 admin pages** were reachable anonymously, including the create,
edit and image-upload pages, so the exposure was unauthenticated WRITE access, not only read.

The gate (`admin/require_login.php`) computes the login URL relative to the running script, so it is
correct from `admin/*`, from `admin/reports/*` and from `server/*`, and it does not break an install
served from a subdirectory.

Two root causes were found while fixing this, both of which had been misdiagnosed earlier in the
session:

1. `admin/header.php` emitted a blank line before any `header()` call. Every redirect executed after
   including it was therefore ignored, with a "Cannot modify header information" warning written into
   the HTTP response. That is why the admin login never redirected to the dashboard, and why
   `dashboard.php` and the five `delete_*.php` answered 200 (instead of 302) when accessed
   anonymously. It is now an include-only file that emits zero bytes. The `ob_start()` workaround an
   earlier pass had needed became unnecessary and was removed from the 16 files that carried it.
2. `admin/login.php`'s form posted to `Login.php` (capital L) while the file is `login.php`. Windows
   does not care; the Linux container does, so the admin panel could not be signed into on the deploy
   target at all. A systematic scan of 577 path references found six such case mismatches; all six are
   fixed and the scan now reports zero (commit `fc038ab`).

Correction to an earlier claim in this document: the 200-instead-of-302 behaviour observed during
verification was first written off as a test-harness artifact. It was not. It was root cause 1.

## Still open - reported, not fixed

- 13 path references point at targets that do not exist in any casing: the `index.php` navigation
  block uses `../css/`, `../img/`, `../php/`, `../html/`, and `layouts/footer-php.php` links to
  `./Login.php`. The `index.php` ones are sloppy but WORK, because a browser normalises `/../x` to
  `/x` and the targets do exist at the root. The footer link is genuinely broken when the footer is
  rendered from the admin login page (it resolves to `/admin/Login.php`). Also
  `admin/reports/report_inactive_customers.php` redirects to `/pages/admin/reports.php`, which does
  not exist. All pre-existing, none blocking.
- Four pre-existing bugs reported by the independent verifier and deliberately not fixed: the missing
  `discount_type_cat` / `discount_value_cat` aliases in `admin/products.php`, the undefined
  `$image_names` in `admin/update_images.php`, the `target_type='body'` inconsistency in
  `admin/edit_discount.php`, and the `'ssis'` bind type with five variables in `admin/create_user.php`.

## Acceptance criteria

- [x] `docker compose up` starts the app and MySQL, and the SQL files load with no errors.
- [x] The site loads: home, electric catalog, acoustic catalog, search, product detail.
- [x] The admin panel signs in with the seeded admin and lists users, products, orders and discounts.
- [x] Every admin page, report page and admin JSON endpoint answers 302 to an anonymous caller
      (31 entry points swept: zero non-302) and 200 to an authenticated one.
- [x] Unauthenticated writes are rejected, proven by row counts: `users` and `products` unchanged
      after anonymous POSTs to `create_user.php` and `create_product.php`.
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
- T8 gated the admin surface and fixed the redirect root cause.
- T9 fixed the case-sensitivity breakage that would have made the panel unusable on Linux.

## Verification evidence (observed, not assumed)

Database load - three independent runs, each loading both SQL files into MySQL 8.4:
- 10 tables, 4 stored procedures (`GetOrderDetails`, `mov_n_delete_order`,
  `mov_n_delete_product`, `mov_n_delete_user`), 25 products, `electric=18`, `acustic=7`, 1 admin.
- `CALL GetOrderDetails(1)` returns the six aliases `admin/order_details.php` reads.
- Re-running the seed leaves the counts unchanged (idempotent).
- The FK-safe delete procedures remove children before parents without error.
- Independent column audit against the PHP source: 0 missing columns, 0 invented columns, for all 10
  tables. `GetOrderDetails` aliases match 6/6.
- 177 seeded image paths checked against disk: 0 missing.

Application - the compose stack, verified with HTTP requests:
- `/index.php`, `/php/ElectricGuitars.php` (8 product cards), `/php/AcousticGuitars.php` (7 cards),
  `/php/ProductDestail.php?product_id=1`, `/php/Search.php` (POST "Les Paul" matched a product):
  all HTTP 200.
- Admin: anonymous GET of all 25 gated pages plus `dashboard.php` and the five `delete_*.php` returns
  302 to the login page (31 entry points, zero non-302); following the redirect lands on a 200 login
  page, including from the deep `admin/reports/*` path and from `server/*`.
- Admin authenticated: the served login form's own `action` is used to POST the seeded credentials and
  the response is a 302 to `dashboard.php?admin_log_success=...`; with that session `users.php`,
  `orders.php`, `products.php`, `discount.php`, `reports.php` and `add_product.php` return 200 with
  data, and `report_sales_by_month.php` returns a 14 KB PDF.
- The three admin JSON endpoints redirect anonymously and return their JSON payloads when
  authenticated, so the dashboard charts keep working.
- Anonymous write attempts are rejected and the database row counts are unchanged.
- `docker compose logs web`: no PHP fatal, warning, notice, "headers already sent" or
  "Cannot modify header" lines.
- PHP lint: the whole app and the admin tree parse with zero syntax errors.
- Image case check: 577 path references scanned inside the Linux container (a Windows bind mount is
  case-insensitive and would have hidden the bug); 0 mismatches remain.

Limitations recorded honestly:
- Email delivery was never exercised: no SMTP credentials exist, so only the configuration path was
  checked (the missing-variable failure message was observed).
- The native immutable receipt review is unavailable in this runtime: `gentle-ai review assess`
  returns `unassessable` because only claude-code and codex are eligible for it. Indexed review was
  not performed; verification rests on the delegated writers' observed command output, an independent
  verifier, and the orchestrator's own HTTP checks.
- The repository working tree still holds the 264 MB of source images. The optimization is applied in
  the built image, so the disk weight and the git history are unchanged.
- The subdirectory-install path of the new gate was reasoned from `DOCUMENT_ROOT` arithmetic, not
  exercised against a second vhost.

## Next step

Owner actions, in order:
1. Revoke the Gmail application password in the Google account. It is still present in the git history
   of a public repository, and removing it from the source does not revoke it.
2. Change the seeded admin password (`admin@lenguajes.com` / `admin123`) immediately after the first
   deploy, and remove or rotate the seeded demo customer.
3. Pick the deploy host and follow `DEPLOY.md`. The app is already running locally on
   `http://localhost:8080` via `docker compose up -d --build`.
4. Push the branch and open a PR when ready. The branch is local and unpushed; `main`, `Dev` and the
   existing static deploy are untouched.
