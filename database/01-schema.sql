-- =============================================================================
-- lenguajes - MySQL 8 schema (reconstructed)
-- =============================================================================
-- Reconstruction of the MySQL database for the old PHP + MySQL guitar store.
-- The original database was LOST and no .sql dump existed in the repository.
-- Every table below was re-derived from the SQL strings actually used by the
-- PHP source; the evidence (file:line) is recorded per table.
--
-- Conventions
--   * Engine: InnoDB
--   * Charset: utf8mb4 / utf8mb4_unicode_ci
--   * Monetary values: DECIMAL (never FLOAT)
--   * Application connection: mysqli("localhost","root","","lenguajes")
--     -> server/connection.php:3
--   * Historical quirks are preserved on purpose (column typos, the
--     'electric'/'acustic' vs 'Electric'/'Acoustic' mismatch, camelCase columns).
--     See database/README.md for the list.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS lenguajes
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE lenguajes;

SET NAMES utf8mb4;

-- Make this script idempotent (safe to re-run).
SET FOREIGN_KEY_CHECKS = 0;

DROP PROCEDURE IF EXISTS GetOrderDetails;
DROP PROCEDURE IF EXISTS mov_n_delete_order;
DROP PROCEDURE IF EXISTS mov_n_delete_product;
DROP PROCEDURE IF EXISTS mov_n_delete_user;

DROP TABLE IF EXISTS promotion_targets;
DROP TABLE IF EXISTS products_details;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS body_style;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;


-- -----------------------------------------------------------------------------
-- body_style
--   Flat category list consumed by the discount UI and the storefront.
--     body_id   -> admin/add_discount.php:104, admin/edit_discount.php:177-178
--     body_name -> admin/edit_discount.php:179
-- -----------------------------------------------------------------------------
CREATE TABLE body_style (
    body_id   INT          NOT NULL AUTO_INCREMENT,
    body_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (body_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- admins
--     admin_id, admin_name, admin_email, admin_password
--       -> admin/login.php:15-16 (SELECT) and admin/login.php:22 (bind_result)
--   admin_password stores an MD5 hash: admin/login.php:13 does md5($_POST['password']).
-- -----------------------------------------------------------------------------
CREATE TABLE admins (
    admin_id       INT          NOT NULL AUTO_INCREMENT,
    admin_name     VARCHAR(100) NOT NULL,
    admin_email    VARCHAR(150) NOT NULL,
    admin_password VARCHAR(255) NOT NULL,
    PRIMARY KEY (admin_id),
    UNIQUE KEY uq_admins_email (admin_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- users
--     user_id, user_name, user_email, user_phone, user_password -> php/Login.php:19
--     user_surname -> admin/create_user.php:49 and admin/edit_users.php:26
--   user_surname is NULLABLE on purpose:
--     * php/Register.php:45 inserts WITHOUT user_surname
--     * admin/create_user.php:49 inserts WITH user_surname
--   Known runtime bug: admin/create_user.php:51 declares bind_param type string
--   'ssis' for 5 bound variables; the column types below are unaffected.
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    user_id       INT          NOT NULL AUTO_INCREMENT,
    user_name     VARCHAR(100) NOT NULL,
    user_surname  VARCHAR(100) NULL,
    user_email    VARCHAR(150) NOT NULL,
    user_phone    VARCHAR(20)  NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (user_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- promotions
--   INSERT list (admin/create_discount.php:24):
--     name, description, discount_type, discount_value, start_date, end_date, active
--   promo_id is the PK used everywhere (discount.php, delete_discount.php, ...).
--   discount_type is written as 'percentage' by both admin forms; the storefront
--   also understands 'fixed' (server/promotion_discount.php:18, admin/products.php:132).
--   start_date/end_date are DATE because the forms use <input type="date"> and
--   validate ^\d{4}-\d{2}-\d{2}$ (admin/create_discount.php:18). They are compared
--   with NOW() BETWEEN start_date AND end_date, so an end_date is inclusive only up
--   to its midnight -> keep seed ranges wide.
-- -----------------------------------------------------------------------------
CREATE TABLE promotions (
    promo_id       INT           NOT NULL AUTO_INCREMENT,
    name           VARCHAR(150)  NOT NULL,
    description    VARCHAR(255)  NULL,
    discount_type  VARCHAR(30)   NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    start_date     DATE          NOT NULL,
    end_date       DATE          NOT NULL,
    active         TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (promo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- products
--   INSERT list = the products contract (admin/create_product.php:78-80, 13 cols):
--     product_name, product_name_shrt, product_description_title,
--     product_description, product_price, product_stock, product_category,
--     product_image_stand, product_image2, product_image3, product_image4,
--     product_image5, product_image1
--   Extra columns read elsewhere:
--     body        -> php/ElectricGuitars.php:73, php/ProductDestail.php:81
--     category_id -> admin/products.php:41
--   Both body and category_id reference body_style and are NULLABLE: neither is
--   written by any admin form.
--   product_category is a free string. The admin WRITES 'Electric'/'Acoustic'
--   (admin/create_product.php:45) but the storefront READS 'electric'/'acustic'
--   (php/ElectricGuitars.php:13, php/AcousticGuitars.php:11). Seed uses lowercase.
--   Image semantics (admin/create_product.php:53-90, admin/update_images.php:41-49):
--     product_image_stand <- form "img1"          (portrait / stand image)
--     product_image2..5   <- form "img2".."img5"  (numbered gallery)
--     product_image1      <- form "img6"          (labelled "Landscape Image" = *_volt)
-- -----------------------------------------------------------------------------
CREATE TABLE products (
    product_id                INT           NOT NULL AUTO_INCREMENT,
    product_name              VARCHAR(150)  NOT NULL,
    product_name_shrt         VARCHAR(50)   NOT NULL,
    product_description_title VARCHAR(200)  NULL,
    product_description       TEXT          NULL,
    product_price             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    product_stock             INT           NOT NULL DEFAULT 0,
    product_category          VARCHAR(50)   NOT NULL,
    product_image_stand       VARCHAR(255)  NULL,
    product_image1            VARCHAR(255)  NULL,
    product_image2            VARCHAR(255)  NULL,
    product_image3            VARCHAR(255)  NULL,
    product_image4            VARCHAR(255)  NULL,
    product_image5            VARCHAR(255)  NULL,
    body                      INT           NULL,
    category_id               INT           NULL,
    PRIMARY KEY (product_id),
    KEY idx_products_category (product_category),
    KEY idx_products_body (body),
    KEY idx_products_category_id (category_id),
    CONSTRAINT fk_products_body
        FOREIGN KEY (body) REFERENCES body_style (body_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES body_style (body_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- products_details
--   READ ONLY: php/ProductDestail.php:16 (and the unused copy php/test.php:20).
--   No PHP file ever INSERTs into it, so these columns were never validated at
--   runtime. Exact read list (1:1 with product_id):
--     product_color (121), product_bodystyle (171), product_top (172),
--     product_bodyshape (173), product_bodymaterial (176), product_finish (177),
--     product_profile (185), product_lenght (186, historical typo),
--     product_neckmaterial (187), product_fingerboard (190),
--     product_nutmaterial (191), product_strapbuttons (200),
--     product_switchtip (201), product_brige (204, historical typo),
--     product_switchwasher (205), product_neckpickup (213),
--     product_pickupselector (214), product_outputJack (215, camelCase),
--     product_bridgepickup (218), product_controls (219),
--     product_stringgauge (227), product_case (228), product_accessories (229).
--   All values are free text; 02-seed.sql fills them with placeholder specs.
-- -----------------------------------------------------------------------------
CREATE TABLE products_details (
    product_id             INT          NOT NULL,
    product_color          VARCHAR(255) NULL,
    product_bodystyle      VARCHAR(150) NULL,
    product_top            VARCHAR(150) NULL,
    product_bodyshape      VARCHAR(150) NULL,
    product_bodymaterial   VARCHAR(150) NULL,
    product_finish         VARCHAR(150) NULL,
    product_profile        VARCHAR(150) NULL,
    product_lenght         VARCHAR(150) NULL,
    product_neckmaterial   VARCHAR(150) NULL,
    product_fingerboard    VARCHAR(150) NULL,
    product_nutmaterial    VARCHAR(150) NULL,
    product_strapbuttons   VARCHAR(150) NULL,
    product_switchtip      VARCHAR(150) NULL,
    product_brige          VARCHAR(150) NULL,
    product_switchwasher   VARCHAR(150) NULL,
    product_neckpickup     VARCHAR(150) NULL,
    product_pickupselector VARCHAR(150) NULL,
    product_outputJack     VARCHAR(150) NULL,
    product_bridgepickup   VARCHAR(150) NULL,
    product_controls       VARCHAR(150) NULL,
    product_stringgauge    VARCHAR(150) NULL,
    product_case           VARCHAR(150) NULL,
    product_accessories    VARCHAR(150) NULL,
    PRIMARY KEY (product_id),
    CONSTRAINT fk_details_product
        FOREIGN KEY (product_id) REFERENCES products (product_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- orders
--   INSERT list (server/place_order.php:27): order_cost, order_status, user_id,
--   user_phone, user_city, user_address, order_date.
--   Also updated by admin/edit_order.php:53 and read by php/Account.php:46.
--   order_status values (admin/edit_order.php:98-101):
--     'not paid' | 'paid' | 'shipped' | 'delivered'
--   order_date is DATETIME: server/place_order.php:23 writes a full datetime,
--   while admin/edit_order.php:56 writes only 'Y-m-d' -> keep DATETIME.
-- -----------------------------------------------------------------------------
CREATE TABLE orders (
    order_id     INT           NOT NULL AUTO_INCREMENT,
    order_cost   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    order_status VARCHAR(30)   NOT NULL DEFAULT 'not paid',
    user_id      INT           NOT NULL,
    user_phone   VARCHAR(20)   NULL,
    user_city    VARCHAR(100)  NULL,
    user_address VARCHAR(255)  NULL,
    order_date   DATETIME      NOT NULL,
    PRIMARY KEY (order_id),
    KEY idx_orders_user (user_id),
    KEY idx_orders_date (order_date),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- order_items
--   INSERT list (server/place_order.php:55): order_id, product_id, product_name,
--   product_image, product_price, user_id, item_date.
--   item_id is the PK used by admin/edit_item.php and admin/delete_item.php.
--   item_date is DATETIME (admin/edit_item.php:117 uses strtotime()).
-- -----------------------------------------------------------------------------
CREATE TABLE order_items (
    item_id       INT           NOT NULL AUTO_INCREMENT,
    order_id      INT           NOT NULL,
    product_id    INT           NOT NULL,
    product_name  VARCHAR(150)  NOT NULL,
    product_image VARCHAR(255)  NULL,
    product_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    user_id       INT           NOT NULL,
    item_date     DATETIME      NOT NULL,
    PRIMARY KEY (item_id),
    KEY idx_items_order (order_id),
    KEY idx_items_product (product_id),
    KEY idx_items_user (user_id),
    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id) REFERENCES orders (order_id),
    CONSTRAINT fk_items_product
        FOREIGN KEY (product_id) REFERENCES products (product_id),
    CONSTRAINT fk_items_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- payments
--   WRITE ONLY: server/complete_payment.php:37 inserts
--     (order_id, user_id, transaction_id, payment_date).
--   No PHP file ever reads this table, and no code references a payment id, so
--   this reconstruction does NOT invent a surrogate column: order_id (one payment
--   per order) is the primary key. See database/README.md -> "Inferred".
-- -----------------------------------------------------------------------------
CREATE TABLE payments (
    order_id       INT          NOT NULL,
    user_id        INT          NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payment_date   DATETIME     NOT NULL,
    PRIMARY KEY (order_id),
    KEY idx_payments_user (user_id),
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders (order_id),
    CONSTRAINT fk_payments_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- promotion_targets
--   Inserted as exactly three columns (admin/create_discount.php:43,
--   admin/edit_discount.php:50): promo_id, target_type, target_id.
--   No surrogate id is implied or referenced anywhere, so the composite
--   (promo_id, target_type, target_id) is the primary key.
--   target_id is polymorphic -> NO foreign key on it:
--     target_type='product'  -> products.product_id   (php/ElectricGuitars.php:88,
--                                                      php/ProductDestail.php:64)
--     target_type='category' -> body_style.body_id    (php/ElectricGuitars.php:72,
--                                                      php/ProductDestail.php:79,
--                                                      admin/products.php:41)
--     target_type='body'     -> written by admin/edit_discount.php:57 but NEVER read.
--   promo_id cascades on delete because admin/delete_discount.php:18 deletes the
--   promotions row directly without touching its targets.
-- -----------------------------------------------------------------------------
CREATE TABLE promotion_targets (
    promo_id    INT         NOT NULL,
    target_type VARCHAR(30) NOT NULL,
    target_id   INT         NOT NULL,
    PRIMARY KEY (promo_id, target_type, target_id),
    KEY idx_targets_lookup (target_type, target_id),
    CONSTRAINT fk_targets_promo
        FOREIGN KEY (promo_id) REFERENCES promotions (promo_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- Stored procedures called by the admin panel
-- =============================================================================

-- GetOrderDetails(?) -- admin/order_details.php:6
-- Must expose exactly these aliases (read at admin/order_details.php):
--   order_id (57), product_id (63), product_name (69), product_photo (75),
--   price (81), item_id (87, 93).
-- admin/order_details.php:75 renders "../img/" . product_photo, so product_photo
-- carries the order_items.product_image value.
DELIMITER $$
CREATE PROCEDURE GetOrderDetails(IN p_order_id INT)
BEGIN
    SELECT oi.order_id      AS order_id,
           oi.product_id    AS product_id,
           oi.product_name  AS product_name,
           oi.product_image AS product_photo,
           oi.product_price AS price,
           oi.item_id       AS item_id
    FROM order_items oi
    WHERE oi.order_id = p_order_id;
END$$
DELIMITER ;


-- mov_n_delete_order(?) -- admin/delete_order.php:18
-- RECONSTRUCTION: the original body is unrecoverable. The name implies "move then
-- delete" into an archive table, but NO archive table is referenced by any PHP
-- file in this repository. This is therefore a minimal, FK-safe delete that
-- removes the dependent rows first (payments, then order_items, then the order).
DELIMITER $$
CREATE PROCEDURE mov_n_delete_order(IN p_order_id INT)
BEGIN
    DELETE FROM payments    WHERE order_id = p_order_id;
    DELETE FROM order_items WHERE order_id = p_order_id;
    DELETE FROM orders      WHERE order_id = p_order_id;
END$$
DELIMITER ;


-- mov_n_delete_product(?) -- admin/delete_product.php:14
-- RECONSTRUCTION: same as mov_n_delete_order; no archive table exists in the code.
-- Deletes order_items first (they FK-reference products), then the 1:1 details
-- row, then any promotion targeting the product, then the product itself.
DELIMITER $$
CREATE PROCEDURE mov_n_delete_product(IN p_product_id INT)
BEGIN
    DELETE FROM order_items      WHERE product_id = p_product_id;
    DELETE FROM products_details WHERE product_id = p_product_id;
    DELETE FROM promotion_targets
        WHERE target_type = 'product' AND target_id = p_product_id;
    DELETE FROM products         WHERE product_id = p_product_id;
END$$
DELIMITER ;


-- mov_n_delete_user(?) -- admin/delete_user.php:16
-- RECONSTRUCTION: same as mov_n_delete_order; no archive table exists in the code.
-- Deletes order_items and payments first, then orders, then the user.
DELIMITER $$
CREATE PROCEDURE mov_n_delete_user(IN p_user_id INT)
BEGIN
    DELETE FROM payments    WHERE user_id = p_user_id;
    DELETE FROM order_items WHERE user_id = p_user_id;
    DELETE FROM orders      WHERE user_id = p_user_id;
    DELETE FROM users       WHERE user_id = p_user_id;
END$$
DELIMITER ;
