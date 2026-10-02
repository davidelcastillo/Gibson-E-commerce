<?php
/**
 * Backwards-compatible connection include.
 *
 * Many scripts pull this file through a relative path (connection.php,
 * ./connection.php, ../server/connection.php, ../../server/connection.php).
 * It must therefore keep this exact name and location, and resolve its own
 * dependencies with __DIR__ rather than relative to the current working
 * directory, so it works no matter where the including script runs from.
 *
 * The contract stays the same: after the include, $conn is an open mysqli
 * object, and a failed connection stops the request with
 * "Couldn't connect to database".
 */

require_once __DIR__ . '/../config/database.php';
