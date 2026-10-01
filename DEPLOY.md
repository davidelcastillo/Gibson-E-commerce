# Deploy the Gibson store (PHP + MySQL)

This repository is the recovered PHP + MySQL guitar-store app. It runs as a container on any host that supports Docker, backed by a MySQL 8 server. It cannot run on Vercel or Netlify: neither offers an official PHP runtime, and neither offers MySQL. Those platforms can only host a static showcase of the site, not this application.

Nothing in this guide has been deployed yet. Follow it top to bottom.

## Quick path

1. Run it locally: `docker compose up -d --build`, then open `http://localhost:8080`.
2. Push the repository to GitHub (never commit `.env`).
3. On the host, add a MySQL 8 instance and point the web service at it with the environment variables in [Section 4](#4-environment-variables).
4. Load `database/01-schema.sql` then `database/02-seed.sql` into that database ([Section 5](#5-provision-the-database-on-a-managed-mysql)).
5. Do the [security checklist](#7-security-checklist-before-the-url-is-public), then run the [post-deploy checks](#8-post-deploy-verification).

## 1. What this is and what runs where

| Item | Value |
|------|-------|
| Application | PHP 8.2 + Apache, MySQL 8 |
| Runtime | A single `web` container built from the root `Dockerfile` |
| Database | MySQL 8.4 (`mysql:8.4`), loaded from `database/01-schema.sql` and `database/02-seed.sql` |
| Local URL | `http://localhost:8080` (host port configurable with `WEB_PORT`) |
| Vercel / Netlify | Not usable for the running app: no official PHP runtime, no MySQL. Static showcase only. |

The repository root is the web root. `index.php` lives at the root; pages live in `php/`, `admin/` and `html/`.

## 2. Run it locally with Docker

### Start

```powershell
docker compose up -d --build
```

Open `http://localhost:8080`.

The `web` service waits for the `db` healthcheck before it starts. On the very first start, MySQL initializes its data directory and only then loads the SQL files; this can take several minutes on a slow disk. That is why the compose healthcheck uses a long `retries` window (300 attempts at 5s). Do not interrupt it.

Useful commands:

```powershell
docker compose ps                 # service state and health
docker compose logs -f web        # Apache / PHP output
docker compose logs -f db         # MySQL output
docker compose down               # stop, KEEP all data
```

### Recreate the database from scratch

```powershell
docker compose down -v
docker compose up -d --build
```

CRITICAL: `docker compose down -v` deletes the named volumes `gibson_mysql_data` and `gibson_app_images`. The schema and seed are executed by the MySQL entrypoint **only on a first start against an empty data directory**. So:

- After `down -v`, a plain `docker compose up` reloads the schema and seed.
- A plain `docker compose up` **on an existing, non-empty `mysql_data` volume does NOT re-run the SQL**. Editing the `.sql` files changes nothing until the volume is removed.

The compose project is named `gibson`, so the volumes are `gibson_mysql_data` and `gibson_app_images`.

### Pick up the optimized images

The `Dockerfile` builds a lightweight copy of `img/` (downscaled to max 1600px and quantized, same filenames and extensions). The `app_images` volume is seeded from the image only the first time it is created. If `app_images` still holds the pre-optimization images, recreate the volume:

```powershell
docker compose down
docker volume rm gibson_app_images
docker compose up -d --build
```

## 3. Deploy to a Docker host

### 3.1 Primary path: Railway

Railway detects and uses a `Dockerfile` at the root of the repository (the file must be named `Dockerfile` with a capital D). See the documentation URLs in [Section 11](#11-provider-documentation-used).

1. Push the branch to GitHub. Confirm `.env` is not tracked (it is already in `.gitignore`).
2. In Railway, create a project and choose **Deploy from GitHub repo**, then select this repository and the branch to deploy.
3. Railway builds the root `Dockerfile` as the web service. No build command or start command is needed.
4. Add a managed MySQL service to the same project: press `Ctrl`/`Cmd` + `K` and pick a database, or click **+ New** on the project canvas and select **Database -> MySQL**.
5. Open the **web** service's Variables tab. Add every variable from [Section 4](#4-environment-variables) that applies. Map the database values using Railway reference variables to the MySQL service's exposed variables:

   | Web service variable | Value (reference) |
   |----------------------|-------------------|
   | `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
   | `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
   | `DB_USER` | `${{MySQL.MYSQLUSER}}` |
   | `DB_PASS` | `${{MySQL.MYSQLPASSWORD}}` |
   | `DB_NAME` | `${{MySQL.MYSQLDATABASE}}` |
   | `PORT` | `80` |

   Replace `MySQL` with the actual service name shown in your project. `${{ServiceName.VARIABLE}}` is Railway's reference-variable syntax. `PORT=80` is required because Apache listens on port 80: Railway routes public traffic to the port named by `PORT`, and a container listening on an explicitly defined port must declare that port.
6. Add the SMTP and mail variables from [Section 4](#4-environment-variables) so the contact form and registration/payment emails work.
7. Attach a volume for uploaded images: in the web service, open **Settings -> Volumes** (or the service's volume section) and add a volume mounted at `/var/www/html/img`. See [Section 6](#6-persistent-uploads). Each service can have only one volume.
8. Expose the service: **Settings -> Networking -> Public Networking -> Generate Domain**. Railway provides a `*.up.railway.app` domain with automatic SSL.

After the first deploy, load the SQL into the managed MySQL ([Section 5](#5-provision-the-database-on-a-managed-mysql)) because the compose init-file mount does not exist on Railway; only the compose `db` service auto-loads it.

### 3.2 Secondary path: any Docker host or VPS

1. Build the image from the repository root:
   ```powershell
   docker build -t gibson-store .
   ```
2. Provide a MySQL 8 instance (managed or self-hosted) and load the schema and seed ([Section 5](#5-provision-the-database-on-a-managed-mysql)).
3. Run the web container. Pass every variable from [Section 4](#4-environment-variables) with `-e`, and mount a volume at `/var/www/html/img`:
   ```powershell
   docker run -d --name gibson-web --restart unless-stopped `
     -p 8080:80 `
     -v gibson_images:/var/www/html/img `
     -e DB_HOST=your-db-host `
     -e DB_PORT=3306 `
     -e DB_USER=your-db-user `
     -e DB_PASS=your-db-password `
     -e DB_NAME=lenguajes `
     -e SMTP_HOST=smtp.gmail.com `
     -e SMTP_PORT=465 `
     -e SMTP_SECURE=ssl `
     -e SMTP_USER=your-smtp-user `
     -e SMTP_PASS=your-smtp-app-password `
     -e MAIL_FROM=your-smtp-user `
     -e MAIL_TO=your-contact-recipient `
     gibson-store
   ```
4. Put a reverse proxy with TLS in front (nginx, Caddy, or your host's load balancer) and proxy to the container's published port. On a VPS you do not need `PORT`; the host port mapping handles routing.

### 3.3 Provider claims that remain unverified

- The exact MySQL default database name created by Railway's managed MySQL is not asserted here. Read `MYSQLDATABASE` from the service variables and use it (via the reference above) rather than guessing.
- Whether a newly attached Railway volume is seeded with the image's baked-in `img/` content is not documented as Docker named-volume seeding is. Verify with the `/img/...` check in [Section 8](#8-post-deploy-verification); if seeded images 404, copy the images into the volume or omit the volume and accept that uploads do not persist.
- Railway CLI log commands are not asserted here. Use the service's Deploy Logs in the dashboard, or confirm CLI usage against the current Railway CLI documentation.

## 4. Environment variables

The application reads these names exactly. A real environment variable always wins over a root `.env` file. With nothing set, the app falls back to the XAMPP defaults (`localhost` / `root` / empty password / `lenguajes`).

| Variable | Required | Default | Example | Notes |
|----------|----------|---------|---------|-------|
| `DB_HOST` | Yes (deploy) | `localhost` | `db` (compose) or managed host | MySQL host. In compose this defaults to the `db` service. |
| `DB_PORT` | Yes (deploy) | `3306` | `3306` | MySQL port. |
| `DB_USER` | Yes (deploy) | `root` | `root` | MySQL user. |
| `DB_PASS` | Yes (deploy) | empty | `change-me` | MySQL password. Empty is valid locally (XAMPP). |
| `DB_NAME` | Yes (deploy) | `lenguajes` | `lenguajes` | Database that holds the schema and seed. |
| `SMTP_HOST` | Yes (mail) | empty | `smtp.gmail.com` | Missing -> `Mail configuration is incomplete`. |
| `SMTP_PORT` | No | `465` | `465` | Defaults to 465 when unset/empty. |
| `SMTP_SECURE` | No | `ssl` | `ssl` | Defaults to `ssl` when unset/empty. |
| `SMTP_USER` | Yes (mail) | empty | `you@gmail.com` | SMTP auth user. |
| `SMTP_PASS` | Yes (mail) | empty | `app-password` | SMTP app password. |
| `MAIL_FROM` | Yes (mail) | empty | `you@gmail.com` | Envelope sender. |
| `MAIL_TO` | Yes (contact form) | empty | `you@gmail.com` | Recipient for the contact form. Registration and payment emails go to the buyer, not to `MAIL_TO`. |
| `MYSQL_ROOT_PASSWORD` | Compose only | `gibson_local_root` | `change-me` | Root password for the local `db` service. Must match `DB_PASS` when `DB_USER=root`. Not used on managed MySQL. |
| `WEB_PORT` | Compose only | `8080` | `8080` | Host port published by compose. Not used on managed hosts. |

`.env.example` documents the same keys. Note: in the environment where this guide was written, reading `.env.example` was blocked by a local permission rule, so the names above were derived from `docker-compose.yml`, `config/database.php`, `config/env.php`, `server/send_email.php`, `php/Register.php`, and `server/complete_payment.php`, and cross-checked against the code that reads each variable.

## 5. Provision the database on a managed MySQL

The compose `db` service auto-loads the SQL only on its first start. On a managed MySQL you load the files yourself. Use a throwaway client container so no local MySQL client is required.

PowerShell 5.1 has no `<` input redirection, so wrap each load in `cmd /c "... < file"`.

```powershell
# Schema first
cmd /c "docker run --rm -i mysql:8.4 mysql -h DB_HOST -P 3306 -u DB_USER -pDB_PASS DB_NAME < database\01-schema.sql"

# Seed second
cmd /c "docker run --rm -i mysql:8.4 mysql -h DB_HOST -P 3306 -u DB_USER -pDB_PASS DB_NAME < database\02-seed.sql"
```

`01-schema.sql` drops and recreates its objects, and `02-seed.sql` deletes and re-inserts with explicit ids, so both are safe to re-run.

Confirm success (10 tables, 4 procedures, 25 products):

```powershell
docker run --rm mysql:8.4 mysql -h DB_HOST -P 3306 -u DB_USER -pDB_PASS -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='DB_NAME'; SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema='DB_NAME' AND routine_type='PROCEDURE'; SELECT COUNT(*) FROM DB_NAME.products;"
```

Expected output:

```
10
4
25
```

The four procedures are `GetOrderDetails`, `mov_n_delete_order`, `mov_n_delete_product`, `mov_n_delete_user`. Verified against this repository: the schema defines 10 tables and 4 procedures, and the seed produces 25 products (18 `electric`, 7 `acustic`), 1 admin, and 1 demo customer.

## 6. Persistent uploads

The container filesystem is ephemeral. Product images uploaded from the admin panel (`admin/create_product.php`, `admin/update_images.php`) are written into `/var/www/html/img`. Without a volume mounted at that path, uploaded images are lost on every rebuild or redeploy.

- Railway: attach one volume to the web service mounted at `/var/www/html/img` (Settings -> Volumes). See the caveat in [Section 3.3](#33-provider-claims-that-remain-unverified) about whether the new volume is seeded with the baked-in images.
- Docker host / VPS: pass `-v gibson_images:/var/www/html/img` (as in [Section 3.2](#32-secondary-path-any-docker-host-or-vps)).
- Compose: already mounts `app_images:/var/www/html/img`.

Without the volume: files uploaded through the admin panel disappear on the next deploy. Images baked into the image at build time (the seeded products) keep working until a volume mount hides or replaces that directory.

## 7. Security checklist before the URL is public

Do all of these before sharing the URL.

- [ ] (a) Revoke the Gmail application password in the Google account. It is still present in the git history of this public repository. Removing it from the source does **not** revoke it.
- [ ] (b) Set the SMTP values from the environment with a **new** app password (`SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM`, `MAIL_TO`).
- [ ] (c) Log in at `/admin/login.php` and change the seeded admin password immediately. The seeded hash is public.
- [ ] (d) Remove or rotate the seeded demo customer account.
- [ ] (e) Never commit `.env`. `.gitignore` already ignores it.
- [ ] (f) Note that the legacy code compares unsalted `md5` password hashes. This is weak. It is acceptable only for a demo/portfolio instance holding throwaway data, never for real user data.

## 8. Post-deploy verification

Replace `BASE_URL` with your deployed URL (for example `https://your-app.up.railway.app`).

```powershell
Invoke-WebRequest -UseBasicParsing "$BASE_URL/index.php"                    | Select-Object StatusCode
Invoke-WebRequest -UseBasicParsing "$BASE_URL/php/ElectricGuitars.php"      | Select-Object StatusCode
Invoke-WebRequest -UseBasicParsing "$BASE_URL/php/AcousticGuitars.php"      | Select-Object StatusCode
Invoke-WebRequest -UseBasicParsing "$BASE_URL/admin/login.php"              | Select-Object StatusCode
Invoke-WebRequest -UseBasicParsing "$BASE_URL/php/ProductDestail.php?product_id=1" | Select-Object StatusCode
Invoke-WebRequest -UseBasicParsing "$BASE_URL/img/acustic/acustic_dave.png" | Select-Object StatusCode, Headers
```

| Request | Expected result |
|---------|-----------------|
| `/index.php` | `200`, the store home page HTML. |
| `/php/ElectricGuitars.php` | `200`, product cards rendered (18 seeded electric products). |
| `/php/AcousticGuitars.php` | `200`, product cards rendered (7 seeded acoustic products). |
| `/admin/login.php` | `200`, the admin login form. |
| `/php/ProductDestail.php?product_id=1` | `200`, the product detail page for product 1. |
| `/img/acustic/acustic_dave.png` | `200`, `Content-Type: image/png`. This path is stored in the database (`products.product_image_stand` = `acustic/acustic_dave.png`, rendered as `../img/acustic/...` from `php/`). |

If a page fails, inspect logs:

- Local: `docker compose logs -f web` (Apache/PHP) and `docker compose logs -f db` (MySQL).
- Railway: the web service's Deploy Logs in the dashboard.

A blank page or a 500 usually means a PHP fatal error (check Apache/PHP output); `Couldn't connect to database` is a connection problem (see [Section 9](#9-troubleshooting)).

## 9. Troubleshooting

| Symptom | Cause and fix |
|---------|---------------|
| Database is empty after a redeploy | The schema/seed run only once, on a first start against an empty data directory. If the data volume was reset or replaced by a fresh one without the init files, nothing is loaded. Load `database/01-schema.sql` then `database/02-seed.sql` manually ([Section 5](#5-provision-the-database-on-a-managed-mysql)). On Railway's managed MySQL, the compose init-file mount never applies. |
| App says `Couldn't connect to database` | `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, or `DB_NAME` is wrong or missing. Check what the container actually sees: `docker compose exec web env` and look for `DB_` keys. In compose the host is the service name `db`. On a remote host, confirm the database is reachable from the web container. |
| Catalog shows no products | The storefront queries the literal lowercase values `product_category='electric'` and `product_category='acustic'` (note the historical typo, no `o`). Verify the seed loaded: `SELECT product_category, COUNT(*) FROM products GROUP BY product_category;` (expected 18 `electric`, 7 `acustic`). |
| Images return 404 | Either the database stores a path with no matching file, or a volume mounted at `/var/www/html/img` is empty. Verify the file exists at `/img/<stored path>`. On Docker named volumes, recreate so the volume is seeded from the image: `docker compose down`, `docker volume rm gibson_app_images`, `docker compose up -d --build`. On a managed volume, copy the images into the volume. |
| Mail fails with `Mail configuration is incomplete` | `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`, and `MAIL_FROM` are required (plus `MAIL_TO` for the contact form). Set them from the environment. `SMTP_PORT` defaults to 465 and `SMTP_SECURE` to `ssl`. |
| Public URL returns "Application failed to respond" on Railway | Apache listens on port 80, so set the service variable `PORT=80`. Without it, Railway forwards traffic to a different port. |
| The build stops while optimizing images | The assets stage skips already-optimal files (`pngquant --skip-if-larger`, `|| true`) and never renames files. If it fails, check Docker's build output for the failing tool and the available disk space. |

## 10. The repository's static version

The `main` branch holds an older static, HTML/CSS-only version of this site. It is untouched by this work, so any existing Netlify or GitHub Pages deploy of `main` keeps working. If the portfolio only needs a static showcase instead of the running application, point it at `main`. This branch (`feature/recover-php-deploy`) is the one that runs the real PHP + MySQL app.

## 11. Provider documentation used

Verified against current official Railway documentation:

- Dockerfile detection: `https://docs.railway.com/builds/dockerfiles`
- Managed MySQL service and its connection variables (`MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQL_URL`): `https://docs.railway.com/databases/mysql`
- Reference variables syntax `${{Service.VAR}}`: `https://docs.railway.com/variables`
- Public networking and domain generation: `https://docs.railway.com/guides/public-networking`
- `PORT` handling for explicitly defined ports: `https://github.com/railwayapp/docs/blob/76db91a4/content/docs/public-networking.md`
- Docker Compose to Railway mapping (managed databases, variables, no `depends_on` equivalent): `https://docs.railway.com/guides/docker-compose`
- Volumes (mount path, one volume per service): `https://docs.railway.com/reference/volumes`

Unverified items are listed in [Section 3.3](#33-provider-claims-that-remain-unverified).

## Pre-publish checklist

- [ ] Local stack starts and `http://localhost:8080` loads.
- [ ] Remote database reports 10 tables, 4 procedures, 25 products.
- [ ] All variables from [Section 4](#4-environment-variables) are set on the host.
- [ ] A volume is mounted at `/var/www/html/img`.
- [ ] Leaked Gmail app password is revoked in the Google account.
- [ ] Seeded admin password is changed; demo customer removed or rotated.
- [ ] Post-deploy HTTP checks in [Section 8](#8-post-deploy-verification) pass, including one `/img/...` path.
- [ ] `.env` is not committed.
