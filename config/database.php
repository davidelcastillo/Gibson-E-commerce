<?php
/**
 * MySQL connection for the legacy Gibson store.
 *
 * Reads the connection settings from the environment (config/env.php loads a
 * root .env when one exists, but a real environment variable always wins):
 *
 *   DB_HOST  default "localhost"
 *   DB_PORT  default "3306"
 *   DB_USER  default "root"
 *   DB_PASS  default "" (empty password, the XAMPP default)
 *   DB_NAME  default "lenguajes"
 *
 * On success it exposes an open mysqli object in $conn, the exact contract the
 * rest of the application expects. On failure it stops with the same hard
 * failure message the original server/connection.php used.
 */

require_once __DIR__ . '/env.php';

$dbHost = getenv('DB_HOST');
$dbHost = ($dbHost === false || $dbHost === '') ? 'localhost' : $dbHost;

$dbPort = getenv('DB_PORT');
$dbPort = ($dbPort === false || $dbPort === '') ? '3306' : (int) $dbPort;
$dbPort = ($dbPort > 0) ? $dbPort : 3306;

$dbUser = getenv('DB_USER');
$dbUser = ($dbUser === false || $dbUser === '') ? 'root' : $dbUser;

// An empty password is a valid value (XAMPP); only an unset variable means "".
$dbPass = getenv('DB_PASS');
$dbPass = ($dbPass === false) ? '' : $dbPass;

$dbName = getenv('DB_NAME');
$dbName = ($dbName === false || $dbName === '') ? 'lenguajes' : $dbName;

// On PHP 8.1+ a failed mysqli_connect() throws mysqli_sql_exception instead of
// returning false, so catch it to keep the legacy "or die" message intact.
try {
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
} catch (mysqli_sql_exception $e) {
    $conn = false;
}

if (!$conn) {
    die("Couldn't connect to database");
}
