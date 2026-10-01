# lenguajes database (reconstructed)

`lenguajes` is the MySQL database behind the old PHP + MySQL guitar-store app.
The original database was lost and the repository contained no `.sql` dump, so
this directory rebuilds it as versioned files: `01-schema.sql` (DDL + the four
stored procedures) and `02-seed.sql` (demo data). Every table was re-derived from
the SQL strings actually used by the PHP source; the evidence is recorded as
`file:line` comments inside `01-schema.sql`.

The application connects with `mysqli("localhost","root","","lenguajes")`
(`server/connection.php:3`), so the database must be named `lenguajes`.

## Quick path

1. Start a MySQL 8 server.
2. Load the schema, then the seed, in order.
3. Verify: 10 tables, 4 procedures, 25 products.

```bash
mysql -uroot -p lenguajes < database/01-schema.sql
mysql -uroot -p lenguajes < database/02-seed.sql
```

`01-schema.sql` is idempotent (it drops and recreates objects), and
`02-seed.sql` is safe to re-run (it deletes and re-inserts with explicit ids).

## Default credentials — MUST BE CHANGED

The seed creates one admin so the panel at `/admin/login.php` is reachable.
`admin/login.php` compares `md5($_POST['password'])` against the stored hash.

| Account | Email | Plaintext password | Stored value | Action |
|---------|-------|--------------------|--------------|--------|
| Admin panel | `admin@lenguajes.com` | `admin123` | `0192023a7bbd73250516f069df18b500` | **MUST BE CHANGED before any deployment** |
| Demo customer (storefront) | `john.doe@example.com` | `customer123` | `f4ad231214cb99a985dff0f056a36242` | Demo data only — remove or rotate |

MD5 is not a safe password hash; it is preserved only because the legacy PHP
compares MD5 hashes directly. Treat both accounts as throwaway.

## How to run the load test

This is the verification of record. It uses Docker because PHP/MySQL are not
installed locally.

```bash
docker run --rm -d --name gibson-sqltest \
  -e MYSQL_ROOT_PASSWORD=test -e MYSQL_DATABASE=lenguajes mysql:8.4

# Wait for the server to finish initialising. Probe with an AUTHENTICATED query,
# not `mysqladmin ping`: ping returns 0 even on "Access denied", so it reports
# ready while the entrypoint is still on its temporary server.
until docker exec gibson-sqltest mysql -uroot -ptest -N -e "SELECT 1" >/dev/null 2>&1; do sleep 2; done

docker exec -i gibson-sqltest mysql -uroot -ptest lenguajes < database/01-schema.sql
docker exec -i gibson-sqltest mysql -uroot -ptest lenguajes < database/02-seed.sql
docker exec gibson-sqltest mysql -uroot -ptest lenguajes -e \
  "SHOW TABLES; SHOW PROCEDURE STATUS WHERE Db='lenguajes'; SELECT COUNT(*) AS products FROM products; CALL GetOrderDetails(1);"
docker rm -f gibson-sqltest
```

Windows note: PowerShell 5.1 has no `<` input redirection. Wrap each load in
`cmd /c "docker exec -i ... < database\01-schema.sql"`.

Expected result: 10 tables, the 4 procedures (`GetOrderDetails`,
`mov_n_delete_order`, `mov_n_delete_product`, `mov_n_delete_user`), and
`products = 25`. `CALL GetOrderDetails(1)` returns two rows exposing the exact
aliases `admin/order_details.php` reads: `order_id`, `product_id`,
`product_name`, `product_photo`, `price`, `item_id`.

## Historical quirks the schema encodes

These are intentional. "Fixing" them would break the legacy PHP.

| Quirk | Detail | Source |
|-------|--------|--------|
| Storefront typo | Storefront reads `product_category = 'acustic'` (no `o`) for acoustic guitars; the admin writes `'Acoustic'`. Seed uses lowercase `electric`/`acustic` or the acoustic page returns zero rows. | `php/ElectricGuitars.php:13`, `php/AcousticGuitars.php:11` vs `admin/create_product.php:45` |
| Two category columns | `products` has BOTH `body` and `category_id`, both nullable FKs to `body_style`, and neither is written by any admin form. | `php/ElectricGuitars.php:73`, `php/ProductDestail.php:81`, `admin/products.php:41` |
| Read-only details table | `products_details` is read but never written by any PHP file. Column names include the typos `product_lenght` and `product_brige` and the camelCase `product_outputJack`. | `php/ProductDestail.php:16`, `:186`, `:204`, `:215` |
| Polymorphic promo targets | `promotion_targets` is inserted as exactly three columns `(promo_id, target_type, target_id)`; there is no surrogate id. `target_type='product'`/`'category'` are read, `'body'` is written but never read. | `admin/create_discount.php:43`, `admin/edit_discount.php:57` |
| Promo dates vs NOW() | `start_date`/`end_date` are `DATE` (forms are `<input type="date">`) but compared with `NOW() BETWEEN start_date AND end_date`, so an `end_date` is inclusive only up to midnight. Seed uses a wide range. | `admin/create_discount.php:18`, `admin/products.php:40` |
| Optional surname | `users.user_surname` is omitted by storefront registration but required by the admin form, so it must be nullable. | `php/Register.php:45` vs `admin/create_user.php:49` |
| create_user bug | `admin/create_user.php:51` passes the type string `'ssis'` for 5 bound variables — a real runtime bug in the legacy code, not reproduced in the schema. | `admin/create_user.php:51` |
| Full datetime vs date | `server/place_order.php` writes a full datetime into `order_date`, while `admin/edit_order.php` writes only `Y-m-d`; the column stays `DATETIME`. | `server/place_order.php:23`, `admin/edit_order.php:56` |
| Write-only payments | `payments` is inserted by the payment flow and never read; no code references a payment id. | `server/complete_payment.php:37` |
| Delete procedures | The original `mov_n_delete_*` bodies are unrecoverable. They implied "move then delete" into archive tables, but **no archive table is referenced anywhere in the code**, so they are reconstructed as minimal FK-safe deletes. | `admin/delete_order.php:18`, `admin/delete_product.php:14`, `admin/delete_user.php:16` |

A side effect worth knowing: `admin/delete_discount.php` deletes a `promotions`
row directly, so `promotion_targets.promo_id` uses `ON DELETE CASCADE` or that
button would fail once a promotion has targets.

## Inferred vs read (confidence)

| Item | Basis | Confidence |
|------|-------|-----------|
| All data columns | Read from real PHP `INSERT`/`SELECT`/`UPDATE` strings | High |
| `payments` primary key = `order_id` | Inferred (one payment per order); no surrogate key is referenced by code, so none was invented | Medium |
| `products_details` values | Placeholder specifications — original rows are unrecoverable | Low (values) / High (columns) |
| Column types and lengths | Inferred from PHP validation and usage patterns | Medium–High |
| `mov_n_delete_*` bodies | Reconstructed as FK-safe deletes | Medium |
| Seed names/prices/stock, `body_style` rows | Plausible demo data derived from filenames | N/A (illustrative) |

## Checklist

- [ ] MySQL 8 server reachable and `lenguajes` created.
- [ ] `01-schema.sql` loads with no errors; 10 tables and 4 procedures exist.
- [ ] `02-seed.sql` loads with no errors; `products = 25` after one or many runs.
- [ ] `CALL GetOrderDetails(1)` returns the six expected aliases.
- [ ] All seeded `img/...` paths resolve on disk (150 product paths, 0 missing).
- [ ] Admin password changed from the default.

## Next step

Load the schema into the real environment, then change the admin credential and
point `server/connection.php` at the target host before deploying.
