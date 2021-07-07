<?php

require_once 'autoload.php';

$status_codes = [
    "OK" => [0, "Success"],
    "INVALID_DATA" => [1, "Wrong request data"],
    "INVALID_SCOPE" => [2, "Wrong requested scope"],
    "INVALID_METHOD" => [3, "Wrong requested method"],
    "PASSWORD_NOT_SAME" => [4, "Different passwords"],
    "REGISTER_ERROR" => [5, "Register error"],
    "AUTH_ERROR" => [6, "Auth error: wrong username/password"],
    "SECURITY_KEY_INVALID" => [7, "Security key invalid"],
    "INVALID_ACTION" => [8, "Invalid action"],
    "DB_ERROR" => [9, "Database error"],
];

$data = file_get_contents('php://input');
$data = json_decode($data, true);

$json_response["status"] = $status_codes['INVALID_DATA'];

if(isset($data)) {
    $json_response["status"] = $status_codes['OK'];

    $secauth = new \Components\Auth\SecAuth($mysqli);
    $auth = new \Components\Auth\Auth($mysqli, $salt['password'], $salt['sec_auth']);
    $tests = new \Components\Tests\Tests($mysqli);

    if($data['scope'] == "auth") {
        if($data['method'] == "register") {
            $email = $data['email'];
            $password = $data['password'];
            $passwordRepeat = $data['passwordRepeat'];

            if($password != $passwordRepeat) {
                $json_response["status"] = $status_codes['PASSWORD_NOT_SAME'];
            } else {
                $registration = $auth->Register($email, $password, false);
                if($registration) {
                    setcookie('security_key', $registration->GetKey(), time()+60*60*24*30); // valid cookie 30 days
                } else {
                    $json_response["status"] = $status_codes['REGISTER_ERROR'];
                }
            }
        } else if($data['method'] == "login") {
            $email = $data['email'];
            $password = $data['password'];

            $authorization = $auth->Login($email, $password);
            if($authorization) {
                setcookie('security_key', $authorization->GetKey(), time()+60*60*24*30); // valid cookie 30 days
            } else {
                $json_response["status"] = $status_codes['AUTH_ERROR'];
            }
        } else if($data['method'] == "logout") {
            $seckey = $data['security_key'];
            $seckey = $secauth->SearchKey($seckey);

            if(!$seckey || !$secauth->RemoveKey($seckey)) {
                $json_response["status"] = $status_codes['SECURITY_KEY_INVALID'];
            }
        } else if($data['method'] == "is_valid") {
            $seckey = $data['security_key'];

            if(!$secauth->SearchKey($seckey)) {
                $json_response["status"] = $status_codes['SECURITY_KEY_INVALID'];
            }
        } else {
            $json_response["status"] = $status_codes['INVALID_METHOD'];
        }
    } else if($data['scope'] == "forms") {
        if($data['method'] == "library_new") {
            if($form = FormLoader::LoadForm('form_new_library'))
                $json_response["form"] = $form;

            // todo actions (save)
        } else if($data['method'] == "library_edit") {
            $library = $tests->GetLibraryByID((int) $data['library_id']);

            if($library) {
                if(empty($data['action'])) {
                    if($form = FormLoader::LoadForm('form_edit_library')) {
                        $cgTasksInTable = new CGTasksInTable($tests->GetTasksByLibraryID($library->GetID()));

                        $form = preg_replace("/(%LIBRARY_ID%)/", $library->GetID(), $form);
                        $form = preg_replace("/(%LIBRARY_TITLE%)/", $library->GetTitle(), $form);
                        $form = preg_replace("/(%LIBRARY_TASKS%)/", $cgTasksInTable->GenerateHTML(), $form);

                        $json_response["form"] = $form;
                    }

                    if($js = FormLoader::LoadJS('form_edit_library'))
                        $json_response["js"] = $js;
                } else if($data['action'] == "save") {
                    if(!empty($data['new_title'])) {
                        $library->SetTitle($data['new_title']);
                        if(!$tests->UpdateLibrary($library)) {
                            $json_response["status"] = $status_codes['DB_ERROR'];
                        }
                    } else {
                        $json_response["status"] = $status_codes['INVALID_DATA'];
                    }
                } else if($data['action'] == "delete") {
                    if(!$tests->DeleteLibrary($library, true)) {
                        $json_response["status"] = $status_codes['DB_ERROR'];
                    }
                } else {
                    $json_response["status"] = $status_codes['INVALID_ACTION'];
                }
            } else {
                $json_response["status"] = $status_codes['INVALID_DATA'];
            }
        } else if($data['method'] == "task_edit") {
            $task = $tests->GetTaskByID((int) $data['task_id']);

            if($task) {
                if(empty($data['action'])) {
                    if($form = FormLoader::LoadForm('form_edit_task')) {
                        $form = preg_replace("/(%TASK_ID%)/", $task->GetID(), $form);
                        $form = preg_replace("/(%TASK_TITLE%)/", $task->GetTitle(), $form);
                        $form = preg_replace("/(%TASK_QUESTION%)/", $task->GetQuestion(), $form);
                        $form = preg_replace("/(%TASK_OUTPUT%)/", $task->GetExpectedOutput(), $form);

                        $json_response["form"] = $form;
                    }

                    // TODO
                    if($js = FormLoader::LoadJS('form_edit_library'))
                        $json_response["js"] = $js;
                } else if($data['action'] == "delete") {
                    if(!$tests->DeleteTask($task)) {
                        $json_response["status"] = $status_codes['DB_ERROR'];
                    }
                } else {
                    $json_response["status"] = $status_codes['INVALID_ACTION'];
                }
            } else {
                $json_response["status"] = $status_codes['INVALID_DATA'];
            }
        } else if($data['method'] == "side_menu") {
            $libraries = $tests->GetLibraries();

            $json_response["form"] = (new CGSideMenu($libraries))->GenerateHTML();
        } else {
            $json_response["status"] = $status_codes['INVALID_METHOD'];
        }
    } else {
        $json_response["status"] = $status_codes['INVALID_SCOPE'];
    }

}

echo json_encode($json_response, JSON_UNESCAPED_UNICODE);
