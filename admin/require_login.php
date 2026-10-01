<?php
// Central admin session gate. Every admin page that must not be reachable
// anonymously requires this file. It is dependency-free, safe to require twice,
// and safe when admin/header.php already called session_start().
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    // Resolve admin/login.php for the location the gate was required from.
    // Inside the admin tree we climb back with "../" so installs served from a
    // subdirectory keep working. Any other caller (for example server/*) gets a
    // document-root absolute path instead, because a bare "login.php" would be
    // resolved by the browser against the caller's own directory (for server/*
    // that meant /server/login.php, which does not exist).
    // admin/users.php          -> "login.php"
    // admin/reports/*.php      -> "../login.php"
    // server/*.php             -> "/admin/login.php"
    $admin_dir  = rtrim(str_replace('\\', '/', __DIR__), '/');
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/');

    $login = 'login.php';
    if ($admin_dir !== '' && strpos($script_dir, $admin_dir) === 0) {
        $sub_path = trim(substr($script_dir, strlen($admin_dir)), '/');
        if ($sub_path !== '') {
            $login = str_repeat('../', substr_count($sub_path, '/') + 1) . 'login.php';
        }
    } else {
        // Called from outside the admin tree: derive an absolute login URL from
        // the document root so it works at "/" and under a subdirectory. When
        // the document root is unusable, /admin/login.php is still correct for
        // the default layout and never emits a wrong relative redirect.
        $doc_root   = isset($_SERVER['DOCUMENT_ROOT'])
            ? rtrim(str_replace('\\', '/', (string) $_SERVER['DOCUMENT_ROOT']), '/')
            : '';
        $admin_real = realpath(__DIR__);
        $admin_real = $admin_real === false ? '' : rtrim(str_replace('\\', '/', $admin_real), '/');
        if ($doc_root !== '' && $admin_real !== '' && strpos($admin_real, $doc_root) === 0) {
            $login = substr($admin_real, strlen($doc_root)) . '/login.php';
        } else {
            $login = '/admin/login.php';
        }
    }

    header('location: ' . $login);
    exit();
}
