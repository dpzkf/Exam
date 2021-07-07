<?php

$code_storage_path = './storage/code/';
$compiler_tools_path = preg_match("/win/i", PHP_OS) ? 'C:/Program Files/Mono/bin/' : '';

$cmd = [
    "compiler" => "mcs",
    "runtime" => "mono",
];

$db = [
    "host" => "localhost",
    "user" => "root",
    "pass" => "#user12345678",
    "db" => "compilecs",
];

$salt = [
    "password" => "YFpFilSAbF4wBM",
    "sec_auth" => "mF3WvOWkU4AHHNl6ecXYwqKAzl90",
];

// $password_salt = "YFpFilSAbF4wBM";

$mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);
if($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');
