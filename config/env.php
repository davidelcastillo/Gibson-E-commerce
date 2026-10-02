<?php
/**
 * Dependency-free .env loader.
 *
 * If a `.env` file exists at the repository root it is parsed as `KEY=VALUE`
 * lines (blank lines and lines starting with `#` are ignored, surrounding
 * quotes are trimmed) and each pair is pushed into the process environment with
 * putenv() and $_ENV.
 *
 * A variable that is ALREADY present in the real environment is never
 * overwritten: the platform (Docker Compose, Railway, Render, ...) always wins
 * over the local .env file. With no .env and no environment variables the app
 * keeps the original XAMPP-style defaults (see config/database.php).
 *
 * This file intentionally uses no Composer autoloader and no external library.
 */

if (!defined('GIBSON_ENV_LOADED')) {
    define('GIBSON_ENV_LOADED', true);

    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

    if (is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);

                // Skip blanks and comments.
                if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                    continue;
                }

                // Tolerate an optional "export " prefix.
                if (strncasecmp($line, 'export ', 7) === 0) {
                    $line = trim(substr($line, 7));
                }

                $separator = strpos($line, '=');
                if ($separator === false) {
                    continue;
                }

                $key = trim(substr($line, 0, $separator));
                $value = trim(substr($line, $separator + 1));

                if ($key === '') {
                    continue;
                }

                // Trim one pair of surrounding single or double quotes.
                $length = strlen($value);
                if ($length >= 2) {
                    $first = $value[0];
                    $last = $value[$length - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $value = substr($value, 1, $length - 2);
                    }
                }

                // Never overwrite a real environment variable: platform env wins.
                if (getenv($key) !== false) {
                    continue;
                }

                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
