<?php

if ($_SERVER['SERVER_NAME'] == 'localhost') {

    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'cms_db');

} else {

    define('DB_HOST', 'sql105.infinityfree.com');
    define('DB_USER', 'if0_42118190');
    define('DB_PASS', '9mJYR29qQX50o');
    define('DB_NAME', 'if0_42118190_cms_db');

}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");