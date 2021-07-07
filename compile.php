<?php

require_once 'autoload.php';
require_once 'mono.php';

$received_code = isset($_POST['code']) ? $_POST['code'] : '';
$response = [];

if(strlen($received_code) > 0) {
    // store file
    $filename = RandomSequence(15, preg_replace('/(\.)|(:)/', '', $_SERVER['REMOTE_ADDR']) . uniqid());
    $filepath = $code_storage_path . $filename;
    $filepath_cs = $filepath . '.cs';
    $filepath_exe = $filepath . '.exe';
    file_put_contents($filepath_cs, $received_code);

    // compile
    $compiler_result = \mono\compile($filepath_cs, $cmd['compiler'], $compiler_tools_path);

    $response['compiler'] = $compiler_result;
    if ($compiler_result['exit_code'] == 0) {
        $runtime_result = \mono\execute($filepath_exe, $cmd['runtime'], $compiler_tools_path);
        $response['runtime'] = $runtime_result;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
