-- =============================================================================
-- lenguajes - seed data (reconstructed)
-- =============================================================================
-- Load AFTER 01-schema.sql:
--   mysql -uroot -p lenguajes < database/01-schema.sql
--   mysql -uroot -p lenguajes < database/02-seed.sql
--
-- Re-runnability strategy (stated choice):
--   * Reset with DELETE (children first) while FOREIGN_KEY_CHECKS = 0.
--   * Re-insert with explicit primary keys.
--   This makes the file safe to re-run repeatedly without TRUNCATE and without
--   relying on AUTO_INCREMENT state. No INSERT ... ON DUPLICATE KEY UPDATE is
--   used, so the loaded state is exactly the rows below on every run.
--
-- All product image paths were derived from the real files under img/ (git
-- ls-files img/), never invented. product_image_stand = <base>.png,
-- product_image1 = <base>_volt.png, product_image2..5 = the four gallery files.
-- =============================================================================

USE lenguajes;
SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM promotion_targets;
DELETE FROM payments;
DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM products_details;
DELETE FROM products;
DELETE FROM promotions;
DELETE FROM body_style;
DELETE FROM users;
DELETE FROM admins;

SET FOREIGN_KEY_CHECKS = 1;


-- -----------------------------------------------------------------------------
-- body_style
--   The app overloads this as the "category" dimension: products.body and
--   products.category_id both point here, and promotion_targets rows with
--   target_type='category' store a body_style.body_id.
--   body_id 1 (Les Paul) is the target of the seeded promotion below.
-- -----------------------------------------------------------------------------
INSERT INTO body_style (body_id, body_name) VALUES
(1, 'Les Paul'),
(2, 'SG'),
(3, 'Explorer'),
(4, 'Flying V'),
(5, 'Dreadnought'),
(6, 'Jumbo'),
(7, 'Square-Shoulder Dreadnought');


-- -----------------------------------------------------------------------------
-- admins
--   One admin so the panel (/admin/login.php) can be reached.
--   admin_password = md5('admin123') = 0192023a7bbd73250516f069df18b500
--   admin/login.php compares md5($_POST['password']) against this column.
--   MUST BE CHANGED before any real deployment (see database/README.md).
-- -----------------------------------------------------------------------------
INSERT INTO admins (admin_id, admin_name, admin_email, admin_password) VALUES
(1, 'Store Administrator', 'admin@lenguajes.com', '0192023a7bbd73250516f069df18b500');


-- -----------------------------------------------------------------------------
-- users
--   One demo customer, required so the demo order below has an owner and so the
--   mov_n_delete_user flow can be exercised. Password md5('customer123').
--   user_surname is present here (admin path) but remains optional for the
--   storefront path (php/Register.php omits it).
-- -----------------------------------------------------------------------------
INSERT INTO users (user_id, user_name, user_surname, user_email, user_phone, user_password) VALUES
(1, 'John', 'Doe', 'john.doe@example.com', '5551234567', 'f4ad231214cb99a985dff0f056a36242');


-- -----------------------------------------------------------------------------
-- promotions + promotion_targets
--   target_type='category' with target_id=1 (Les Paul). Wide date range because
--   the storefront gates on NOW() BETWEEN start_date AND end_date and end_date
--   is inclusive only up to its midnight.
-- -----------------------------------------------------------------------------
INSERT INTO promotions (promo_id, name, description, discount_type, discount_value, start_date, end_date, active) VALUES
(1, 'Les Paul Season', '10% off every Les Paul model', 'percentage', 10.00, '2020-01-01', '2035-12-31', 1);

INSERT INTO promotion_targets (promo_id, target_type, target_id) VALUES
(1, 'category', 1);


-- -----------------------------------------------------------------------------
-- products (25 rows)
--   product_category uses the lowercase values the storefront reads:
--     'acustic' (historical typo, no 'o') for img/acustic
--     'electric' for the four electric folders
--   body and category_id are both set to the matching body_style id so the
--   discount resolves on both the storefront (products.body) and the admin list
--   (products.category_id).
-- -----------------------------------------------------------------------------
INSERT INTO products
(product_id, product_name, product_name_shrt, product_description_title, product_description,
 product_price, product_stock, product_category,
 product_image_stand, product_image1, product_image2, product_image3, product_image4, product_image5,
 body, category_id) VALUES

-- img/acustic
(1, 'Gibson Dave Acoustic', 'Dave Acoustic', 'Dave Signature Acoustic',
 'A hand-finished acoustic built for singer-songwriters: Sitka spruce top, mahogany back and sides, and a warm, balanced voice.',
 2999.00, 6, 'acustic',
 'acustic/acustic_dave.png', 'acustic/acustic_dave_volt.png',
 'acustic/acustic_dave-1.png', 'acustic/acustic_dave-2.png', 'acustic/acustic_dave-3.png', 'acustic/acustic_dave-4.png',
 5, 5),

(2, 'Gibson Hummingbird Faded', 'Hummingbird Faded', 'Hummingbird Faded',
 'The classic square-shoulder dreadnought with a faded finish, delivering rich lows and articulate highs.',
 2799.00, 4, 'acustic',
 'acustic/acustic_humminfaded.png', 'acustic/acustic_humminfaded_volt.png',
 'acustic/acustic_humminfaded1.png', 'acustic/acustic_humminfaded2.png', 'acustic/acustic_humminfaded3.png', 'acustic/acustic_humminfaded4.png',
 7, 7),

(3, 'Gibson J-180', 'J-180', 'J-180',
 'A stage-ready jumbo with maple back and sides and a bright, projective tone.',
 3499.00, 3, 'acustic',
 'acustic/acustic_j180.png', 'acustic/acustic_j180_volt.png',
 'acustic/acustic_j180-1.png', 'acustic/acustic_j180-2.png', 'acustic/acustic_j180-3.png', 'acustic/acustic_j180-4.png',
 6, 6),

(4, 'Gibson J-35', 'J-35', 'J-35',
 'A vintage-inspired dreadnought with a Sitka spruce top and scalloped bracing for open, dynamic tone.',
 2599.00, 5, 'acustic',
 'acustic/acustic_j35.png', 'acustic/acustic_j35_volt.png',
 'acustic/acustic_j35-1.png', 'acustic/acustic_j35-2.png', 'acustic/acustic_j35-3.png', 'acustic/acustic_j35-4.png',
 6, 6),

(5, 'Gibson J-45 Red', 'J-45 Red', 'J-45 Red',
 'The workhorse round-shoulder dreadnought in a vivid red finish, versatile across every genre.',
 2899.00, 7, 'acustic',
 'acustic/acustic_j45red.png', 'acustic/acustic_j45red_volt.png',
 'acustic/acustic_j45red1.png', 'acustic/acustic_j45red2.png', 'acustic/acustic_j45red3.png', 'acustic/acustic_j45red4.png',
 5, 5),

(6, 'Gibson J-45 Standard', 'J-45 Standard', 'J-45 Standard',
 'The iconic round-shoulder dreadnought, with a warm mahogany body and legendary recording tone.',
 2699.00, 8, 'acustic',
 'acustic/acustic_j45standar.png', 'acustic/acustic_j45standar_volt.png',
 'acustic/acustic_j45standar1.png', 'acustic/acustic_j45standar2.png', 'acustic/acustic_j45standar3.png', 'acustic/acustic_j45standar4.png',
 5, 5),

(7, 'Gibson SJ', 'SJ', 'SJ',
 'A super-jumbo acoustic with deep projection and a commanding low end.',
 3299.00, 4, 'acustic',
 'acustic/acustic_sj.png', 'acustic/acustic_sj_volt.png',
 'acustic/acustic_sj-1.png', 'acustic/acustic_sj-2.png', 'acustic/acustic_sj-3.png', 'acustic/acustic_sj-4.png',
 6, 6),

-- img/guitarra_expl
(8, 'Gibson Explorer Black', 'Explorer Black', 'Explorer Black',
 'A radical offset solid body built for high-output rock, finished in gloss black.',
 1899.00, 9, 'electric',
 'guitarra_expl/explorer_negra.png', 'guitarra_expl/explorer_negra_volt.png',
 'guitarra_expl/explorer_negra1.png', 'guitarra_expl/explorer_negra2.png', 'guitarra_expl/explorer_negra3.png', 'guitarra_expl/explorer_negra4.png',
 3, 3),

(9, 'Gibson Explorer White', 'Explorer White', 'Explorer White',
 'The Explorer silhouette in a crisp white gloss finish with hot humbuckers.',
 1949.00, 6, 'electric',
 'guitarra_expl/explorer_white.png', 'guitarra_expl/explorer_white_volt.png',
 'guitarra_expl/explorer_white1.png', 'guitarra_expl/explorer_white2.png', 'guitarra_expl/explorer_white3.png', 'guitarra_expl/explorer_white4.png',
 3, 3),

-- img/guitarra_sg
(10, 'Gibson SG Angus Young', 'SG Angus', 'SG Angus Young',
 'A tribute SG with a slim taper neck and screaming humbuckers, made for the stage.',
 2199.00, 5, 'electric',
 'guitarra_sg/sg_angus.png', 'guitarra_sg/sg_angus_volt.png',
 'guitarra_sg/sg_angus1.png', 'guitarra_sg/sg_angus2.png', 'guitarra_sg/sg_angus3.png', 'guitarra_sg/sg_angus4.png',
 2, 2),

(11, 'Gibson SG Modern Purple', 'SG Modern Purple', 'SG Modern Purple',
 'A modern SG with coil-splittable pickups and a striking purple burst.',
 1799.00, 10, 'electric',
 'guitarra_sg/sg_modernpurple.png', 'guitarra_sg/sg_modernpurple_volt.png',
 'guitarra_sg/sg_modernpurple1.png', 'guitarra_sg/sg_modernpurple2.png', 'guitarra_sg/sg_modernpurple3.png', 'guitarra_sg/sg_modernpurple4.png',
 2, 2),

(12, 'Gibson SG Black', 'SG Black', 'SG Black',
 'The double-cutaway classic in gloss black, fast and aggressive with a biting midrange.',
 1699.00, 12, 'electric',
 'guitarra_sg/sg_negra.png', 'guitarra_sg/sg_negra_volt.png',
 'guitarra_sg/sg_negra1.png', 'guitarra_sg/sg_negra2.png', 'guitarra_sg/sg_negra3.png', 'guitarra_sg/sg_negra4.png',
 2, 2),

(13, 'Gibson SG Standard 61', 'SG Standard 61', 'SG Standard 61',
 'A faithful reissue of the 1961 SG with period-correct pickups and a vintage neck profile.',
 1999.00, 7, 'electric',
 'guitarra_sg/sg_standar61.png', 'guitarra_sg/sg_standar61_volt.png',
 'guitarra_sg/sg_standar611.png', 'guitarra_sg/sg_standar612.png', 'guitarra_sg/sg_standar613.png', 'guitarra_sg/sg_standar614.png',
 2, 2),

-- img/guitarra_v
(14, 'Gibson Flying V Blue', 'Flying V Blue', 'Flying V Blue',
 'The futuristic Flying V in a deep blue finish, with explosive tone and effortless upper-fret access.',
 1799.00, 8, 'electric',
 'guitarra_v/v_blue.png', 'guitarra_v/v_blue_volt.png',
 'guitarra_v/v_blue1.png', 'guitarra_v/v_blue2.png', 'guitarra_v/v_blue3.png', 'guitarra_v/v_blue4.png',
 4, 4),

(15, 'Gibson Flying V Korina', 'Flying V Korina', 'Flying V Korina',
 'A korina-bodied Flying V with warm resonance and vintage-correct hardware.',
 3499.00, 3, 'electric',
 'guitarra_v/v_korina.png', 'guitarra_v/v_korina_volt.png',
 'guitarra_v/v_korina1.png', 'guitarra_v/v_korina2.png', 'guitarra_v/v_korina3.png', 'guitarra_v/v_korina4.png',
 4, 4),

(16, 'Gibson Flying V Black', 'Flying V Black', 'Flying V Black',
 'The Flying V in gloss black, built for players who want a bold look and a bold sound.',
 1749.00, 9, 'electric',
 'guitarra_v/v_negra.png', 'guitarra_v/v_negra_volt.png',
 'guitarra_v/v_negra1.png', 'guitarra_v/v_negra2.png', 'guitarra_v/v_negra3.png', 'guitarra_v/v_negra4.png',
 4, 4),

-- img/guitarras_lp
(17, 'Gibson Les Paul Modern', 'Les Paul Modern', 'Les Paul Modern',
 'A weight-relieved Les Paul with coil-splitting and a compound-radius neck for modern playability.',
 2499.00, 11, 'electric',
 'guitarras_lp/lp_modern.png', 'guitarras_lp/lp_modern_volt.png',
 'guitarras_lp/lp_modern1.png', 'guitarras_lp/lp_modern2.png', 'guitarras_lp/lp_modern3.png', 'guitarras_lp/lp_modern4.png',
 1, 1),

(18, 'Gibson Les Paul Modern Purple', 'LP Modern Purple', 'Les Paul Modern Purple',
 'A purple-burst Les Paul Modern with versatile electronics and a sleek, contoured body.',
 2299.00, 6, 'electric',
 'guitarras_lp/lp_modernpurple.png', 'guitarras_lp/lp_modernpurple_volt.png',
 'guitarras_lp/lp_modernpurple1.png', 'guitarras_lp/lp_modernpurple2.png', 'guitarras_lp/lp_modernpurple3.png', 'guitarras_lp/lp_modernpurple4.png',
 1, 1),

(19, 'Gibson Les Paul Pink', 'Les Paul Pink', 'Les Paul Pink',
 'A bold pink Les Paul with a mahogany body and maple top, loud on looks and tone.',
 2199.00, 5, 'electric',
 'guitarras_lp/lp_pink.png', 'guitarras_lp/lp_pink.png',
 'guitarras_lp/lp_pink1.png', 'guitarras_lp/lp_pink2.png', 'guitarras_lp/lp_pink3.png', 'guitarras_lp/lp_pink4.png',
 1, 1),

(20, 'Gibson Les Paul Purple', 'Les Paul Purple', 'Les Paul Purple',
 'A deep purple Les Paul with warm humbuckers and a classic single-cutaway feel.',
 2249.00, 7, 'electric',
 'guitarras_lp/lp_purple.png', 'guitarras_lp/lp_purple_volt.png',
 'guitarras_lp/lp_purple1.png', 'guitarras_lp/lp_purple2.png', 'guitarras_lp/lp_purple3.png', 'guitarras_lp/lp_purple4.png',
 1, 1),

(21, 'Gibson Les Paul Slash 1', 'LP Slash 1', 'Les Paul Slash 1',
 'A Slash signature Les Paul with hot pickups and a figured top, tuned for soaring leads.',
 3499.00, 4, 'electric',
 'guitarras_lp/lp_slash1.png', 'guitarras_lp/lp_slash1_volt.png',
 'guitarras_lp/lp_slash1_1.png', 'guitarras_lp/lp_slash1_2.png', 'guitarras_lp/lp_slash1_3.png', 'guitarras_lp/lp_slash1_4.png',
 1, 1),

(22, 'Gibson Les Paul Slash 3', 'LP Slash 3', 'Les Paul Slash 3',
 'A Slash signature Les Paul with a dark burst top and a fast, comfortable neck.',
 3399.00, 5, 'electric',
 'guitarras_lp/lp_slash3.png', 'guitarras_lp/lp_slash3_volt.png',
 'guitarras_lp/lp_slash3_1.png', 'guitarras_lp/lp_slash3_2.png', 'guitarras_lp/lp_slash3_3.png', 'guitarras_lp/lp_slash3_4.png',
 1, 1),

(23, 'Gibson Les Paul Slash 7', 'LP Slash 7', 'Les Paul Slash 7',
 'A limited Slash signature Les Paul with premium tonewoods and signature Alnico pickups.',
 3299.00, 3, 'electric',
 'guitarras_lp/lp_slash7.png', 'guitarras_lp/lp_slash7_volt.png',
 'guitarras_lp/lp_slash7_1.png', 'guitarras_lp/lp_slash7_2.png', 'guitarras_lp/lp_slash7_3.png', 'guitarras_lp/lp_slash7_4.png',
 1, 1),

(24, 'Gibson Les Paul Slash Jessica', 'LP Slash Jessica', 'Les Paul Slash Jessica',
 'The Slash Jessica Les Paul, a collector goldtop with signature pickups and a vintage feel.',
 3699.00, 2, 'electric',
 'guitarras_lp/lp_slashjesica.png', 'guitarras_lp/lp_slashjesica_volt.png',
 'guitarras_lp/lp_slashjesica1.png', 'guitarras_lp/lp_slashjesica2.png', 'guitarras_lp/lp_slashjesica3.png', 'guitarras_lp/lp_slashjesica4.png',
 1, 1),

(25, 'Gibson Les Paul Standard 60', 'LP Standard 60', 'Les Paul Standard 60',
 'The 1960-spec Les Paul Standard with a slim taper neck and classic Burstbucker tone.',
 2699.00, 10, 'electric',
 'guitarras_lp/lp_standar60.png', 'guitarras_lp/lp_standar60_volt.png',
 'guitarras_lp/lp_standar601.png', 'guitarras_lp/lp_standar602.png', 'guitarras_lp/lp_standar603.png', 'guitarras_lp/lp_standar604.png',
 1, 1);


-- -----------------------------------------------------------------------------
-- products_details (one row per product)
--   !! PLACEHOLDER SPECIFICATIONS !!
--   The original products_details rows are unrecoverable: this table is READ by
--   php/ProductDestail.php but never written by any PHP file, so no source of
--   truth exists for its values. Every value below is a plausible guitar
--   specification written for the demo data set, not recovered data.
--   Column names include the historical typos product_lenght and product_brige,
--   plus the camelCase product_outputJack, exactly as read by ProductDestail.php.
--   product_color is used as an <img src> by ProductDestail.php:121, so it holds
--   a relative path (from php/) to the product's own stand image.
-- -----------------------------------------------------------------------------
INSERT INTO products_details
(product_id, product_color, product_bodystyle, product_top, product_bodyshape, product_bodymaterial,
 product_finish, product_profile, product_lenght, product_neckmaterial, product_fingerboard,
 product_nutmaterial, product_strapbuttons, product_switchtip, product_brige, product_switchwasher,
 product_neckpickup, product_pickupselector, product_outputJack, product_bridgepickup,
 product_controls, product_stringgauge, product_case, product_accessories) VALUES

-- Acoustic models
(1, '../img/acustic/acustic_dave.png', 'Dreadnought', 'Sitka Spruce', 'Dreadnought', 'Mahogany',
 'Nitrocellulose Lacquer', 'Advanced Response', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Traditional Belly Up', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

(2, '../img/acustic/acustic_humminfaded.png', 'Square-Shoulder Dreadnought', 'Sitka Spruce', 'Square Shoulder', 'Mahogany',
 'Faded Satin Nitrocellulose', 'Advanced Response', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Traditional Belly Up', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

(3, '../img/acustic/acustic_j180.png', 'Jumbo', 'Sitka Spruce', 'Jumbo', 'Maple',
 'Nitrocellulose Lacquer', 'Slim Taper', '25.5 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Adjustable Tune-O-Matic', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

(4, '../img/acustic/acustic_j35.png', 'Dreadnought', 'Sitka Spruce', 'Dreadnought', 'Mahogany',
 'Nitrocellulose Lacquer', 'Advanced Response', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Traditional Belly Up', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

(5, '../img/acustic/acustic_j45red.png', 'Dreadnought', 'Sitka Spruce', 'Round Shoulder', 'Mahogany',
 'Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Traditional Belly Up', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

(6, '../img/acustic/acustic_j45standar.png', 'Dreadnought', 'Sitka Spruce', 'Round Shoulder', 'Mahogany',
 'Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Traditional Belly Up', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

(7, '../img/acustic/acustic_sj.png', 'Jumbo', 'Sitka Spruce', 'Jumbo', 'Rosewood',
 'Nitrocellulose Lacquer', 'Advanced Response', '25.5 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Bone', 'N/A', 'N/A', 'Traditional Belly Up', 'N/A',
 'N/A', 'N/A', 'N/A', 'N/A',
 'N/A', '.012-.053', 'Hardshell Case', 'Gibson Accessory Kit'),

-- Explorer models
(8, '../img/guitarra_expl/explorer_negra.png', 'Explorer', 'Mahogany', 'Explorer', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker 2', '3-way toggle', 'Nickel', 'Burstbucker 3',
 '2 volume, 1 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(9, '../img/guitarra_expl/explorer_white.png', 'Explorer', 'Mahogany', 'Explorer', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker 2', '3-way toggle', 'Nickel', 'Burstbucker 3',
 '2 volume, 1 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

-- SG models
(10, '../img/guitarra_sg/sg_angus.png', 'SG', 'Mahogany', 'Double Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 '490R', '3-way toggle', 'Nickel', '490T',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(11, '../img/guitarra_sg/sg_modernpurple.png', 'SG', 'Mahogany', 'Double Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Asymmetrical Slim Taper', '24.75 in', 'Mahogany', 'Ebony, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker Pro Rhythm', '3-way toggle', 'Nickel', 'Burstbucker Pro Lead',
 '2 volume, 2 tone with coil split', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(12, '../img/guitarra_sg/sg_negra.png', 'SG', 'Mahogany', 'Double Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 '490R', '3-way toggle', 'Nickel', '490T',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(13, '../img/guitarra_sg/sg_standar61.png', 'SG', 'Mahogany', 'Double Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Vintage 60s', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker 61R', '3-way toggle', 'Nickel', 'Burstbucker 61T',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

-- Flying V models
(14, '../img/guitarra_v/v_blue.png', 'Flying V', 'Mahogany', 'Flying V', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker 2', '3-way toggle', 'Nickel', 'Burstbucker 3',
 '2 volume, 1 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(15, '../img/guitarra_v/v_korina.png', 'Flying V', 'Korina', 'Flying V', 'Korina',
 'Gloss Nitrocellulose Lacquer', 'Vintage 50s', '24.75 in', 'Korina', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker 2', '3-way toggle', 'Nickel', 'Burstbucker 3',
 '2 volume, 1 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(16, '../img/guitarra_v/v_negra.png', 'Flying V', 'Mahogany', 'Flying V', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Black', 'Tune-O-Matic', 'Black',
 'Burstbucker 2', '3-way toggle', 'Nickel', 'Burstbucker 3',
 '2 volume, 1 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

-- Les Paul models
(17, '../img/guitarras_lp/lp_modern.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Asymmetrical Slim Taper', '24.75 in', 'Mahogany', 'Ebony, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Burstbucker Pro Rhythm', '3-way toggle', 'Nickel', 'Burstbucker Pro Lead',
 '2 volume, 2 tone with coil split', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(18, '../img/guitarras_lp/lp_modernpurple.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Asymmetrical Slim Taper', '24.75 in', 'Mahogany', 'Ebony, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Burstbucker Pro Rhythm', '3-way toggle', 'Nickel', 'Burstbucker Pro Lead',
 '2 volume, 2 tone with coil split', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(19, '../img/guitarras_lp/lp_pink.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Burstbucker 61R', '3-way toggle', 'Nickel', 'Burstbucker 61T',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(20, '../img/guitarras_lp/lp_purple.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Burstbucker 61R', '3-way toggle', 'Nickel', 'Burstbucker 61T',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(21, '../img/guitarras_lp/lp_slash1.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slash Custom', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Custom Burstbucker Alnico II', '3-way toggle', 'Nickel', 'Custom Burstbucker Alnico II',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(22, '../img/guitarras_lp/lp_slash3.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slash Custom', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Custom Burstbucker Alnico II', '3-way toggle', 'Nickel', 'Custom Burstbucker Alnico II',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(23, '../img/guitarras_lp/lp_slash7.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slash Custom', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Custom Burstbucker Alnico II', '3-way toggle', 'Nickel', 'Custom Burstbucker Alnico II',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(24, '../img/guitarras_lp/lp_slashjesica.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slash Custom', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Custom Burstbucker Alnico II', '3-way toggle', 'Nickel', 'Custom Burstbucker Alnico II',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit'),

(25, '../img/guitarras_lp/lp_standar60.png', 'Les Paul', 'Maple', 'Single Cutaway', 'Mahogany',
 'Gloss Nitrocellulose Lacquer', 'Slim Taper', '24.75 in', 'Mahogany', 'Rosewood, 12 in radius',
 'Graph Tech NuBone', '2', 'Cream', 'Tune-O-Matic', 'Cream',
 'Burstbucker 61R', '3-way toggle', 'Nickel', 'Burstbucker 61T',
 '2 volume, 2 tone', '.010-.046', 'Hardshell Case', 'Gibson Accessory Kit');


-- -----------------------------------------------------------------------------
-- Demo order data
--   Not historically recoverable; included so that:
--     * CALL GetOrderDetails(1) returns real rows,
--     * mov_n_delete_product / mov_n_delete_user can be exercised against rows
--       that actually have order_items / orders (the FK-safe paths).
-- -----------------------------------------------------------------------------
INSERT INTO orders (order_id, order_cost, order_status, user_id, user_phone, user_city, user_address, order_date) VALUES
(1, 5298.00, 'paid', 1, '5551234567', 'Los Angeles', '742 Evergreen Terrace', '2024-06-15 10:30:00');

INSERT INTO order_items (item_id, order_id, product_id, product_name, product_image, product_price, user_id, item_date) VALUES
(1, 1, 1, 'Gibson Dave Acoustic', 'acustic/acustic_dave.png', 2999.00, 1, '2024-06-15 10:30:00'),
(2, 1, 18, 'Gibson Les Paul Modern Purple', 'guitarras_lp/lp_modernpurple.png', 2299.00, 1, '2024-06-15 10:30:00');

INSERT INTO payments (order_id, user_id, transaction_id, payment_date) VALUES
(1, 1, 'DEMO-TXN-0001', '2024-06-15 10:35:00');
