<?php

$code_storage_path = './storage/code/';
$compiler_tools_path = preg_match("/win/i", PHP_OS) ? 'C:/Program Files/Mono/bin/' : '';

$cmd = [
    "compiler" => "mcs",
    "runtime" => "mono",
];
