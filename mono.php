<?php

namespace mono;

function compile($filename, $compiler_name = "mcs", $compiler_root = "") {
    $output = null;
    $exit_code = 0;

    exec("\"$compiler_root$compiler_name\" \"$filename\" 2>&1", $output, $exit_code);

    return [
        "output" => $output,
        "exit_code" => $exit_code,
    ];
}

function execute($filename, $executor_name = "mono", $runtime_root = "") {
    $output = null;
    $exit_code = 0;

    exec("\"$runtime_root$executor_name\" \"$filename\" 2>&1", $output, $exit_code);

    return [
        "output" => $output,
        "exit_code" => $exit_code,
    ];
}
