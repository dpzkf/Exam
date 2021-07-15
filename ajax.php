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
    "NO_AUTH" => [10, "User not authorized"],
    "UNKNOWN_LIBRARY" => [11, "Unknown library"],
    "UNKNOWN_TEST" => [12, "First - select the test in progress"],
    "WRONG_OUTPUT" => [13, "Wrong output!"],
];

$data = file_get_contents('php://input');
$data = json_decode($data, true);

$json_response["status"] = $status_codes['INVALID_DATA'];

if(isset($data)) {
    $json_response["status"] = $status_codes['OK'];

    $secauth = new \Components\Auth\SecAuth($mysqli);
    $auth = new \Components\Auth\Auth($mysqli, $salt['password'], $salt['sec_auth']);
    $tests = new \Components\Tests\Tests($mysqli);
    $progress = new \Components\Progress\Progress($mysqli, $tests);

    $user = null;
    if(!empty($_COOKIE['security_key']) && $secauthkey = $secauth->SearchKey($_COOKIE['security_key'])) {
        $user = $auth->SearchUserByID($secauthkey->GetUserID());
    }

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
            if(empty($data['action'])) {
                if($form = FormLoader::LoadForm('form_new_library'))
                    $json_response["form"] = $form;

                if($js = FormLoader::LoadJS('form_new_library'))
                    $json_response["js"] = $js;
            } else if($data['action'] == "save") {
                if(!empty($data['library_name'])) {
                    $library = new Library(0, $data['library_name'], []);
                    if(!$tests->CreateLibrary($library)) {
                        $json_response["status"] = $status_codes['DB_ERROR'];
                    }
                } else {
                    $json_response["status"] = $status_codes['INVALID_DATA'];
                }
            }
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
        } else if($data['method'] == "task_new") {
            if(empty($data['action']) && !empty($data['library_id'])) {
                $library = $tests->GetLibraryByID($data['library_id']);

                if($form = FormLoader::LoadForm('form_new_task')) {
                    if($library) {
                        $form = preg_replace("/(%LIBRARY_ID%)/", $library->GetID(), $form);
                    }

                    $json_response["form"] = $form;
                }

                if($js = FormLoader::LoadJS('form_new_task'))
                    $json_response["js"] = $js;
            } else if($data['action'] == "save") {
                if(!empty($data['library_id']) && !empty($data['task_name']) && !empty($data['task_question']) && !empty($data['task_output'])) {
                    $library = $tests->GetLibraryByID($data['library_id']);
                    if($library) {
                        $task = new Task(
                            0,
                            $library->GetID(),
                            $data['task_name'],
                            $data['task_question'],
                            $data['task_output'],
                            2000
                        ); // 2 sec max execute time

                        if (!$tests->CreateTask($task)) {
                            $json_response["status"] = $status_codes['DB_ERROR'];
                        }
                    } else {
                        $json_response["status"] = $status_codes['UNKNOWN_LIBRARY'];
                    }
                } else {
                    $json_response["status"] = $status_codes['INVALID_DATA'];
                }
            } else {
                $json_response["status"] = $status_codes['INVALID_ACTION'];
            }
        } else if($data['method'] == "task_edit") {
            $task = $tests->GetTaskByID((int) $data['task_id']);

            if($task) {
                if(empty($data['action'])) {
                    if($form = FormLoader::LoadForm('form_edit_task')) {
                        $form = preg_replace("/(%TASK_ID%)/", $task->GetID(), $form);
                        $form = preg_replace("/(%TASK_TITLE%)/", htmlspecialchars($task->GetTitle()), $form);
                        $form = preg_replace("/(%TASK_QUESTION%)/", htmlspecialchars($task->GetQuestion()), $form);
                        $form = preg_replace("/(%TASK_OUTPUT%)/", htmlspecialchars($task->GetExpectedOutput()), $form);

                        $json_response["form"] = $form;
                    }

                    if($js = FormLoader::LoadJS('form_edit_task'))
                        $json_response["js"] = $js;
                } else if($data['action'] == "save") {
                    if(!empty($data['new_title'])) {
                        $task->SetTitle($data['new_title']);
                        $task->SetQuestion($data['new_question']);
                        $task->SetExpectedOutput($data['new_expected_output']);

                        if(!$tests->UpdateTask($task)) {
                            $json_response["status"] = $status_codes['DB_ERROR'];
                        }
                    } else {
                        $json_response["status"] = $status_codes['INVALID_DATA'];
                    }
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

            $json_response["form"] = (new CGSideMenuTeacher($libraries))->GenerateHTML();
        } else {
            $json_response["status"] = $status_codes['INVALID_METHOD'];
        }
    }  else if($data['scope'] == "progress") {
        if($data['method'] == "activate_library_tests") {
            $library = $tests->GetLibraryByID((int) $data['library_id']);

            if($library) {
                if ($user) {
                    $json_response["dbg"]["lib"] = $library;

                    if ($progress->ActivateLibraryForUser($user, $library)) {
                        setcookie('selected_library', $library->GetID(), time()+60*60*24*30); // valid cookie 30 days
                    } else {
                        $json_response["status"] = $status_codes['NO_AUTH'];
                    }
                } else {
                    $json_response["status"] = $status_codes['NO_AUTH'];
                }
            } else {
                $json_response["status"] = $status_codes['INVALID_DATA'];
            }
        } else if($data['method'] == "get_current_task") {
            if(!empty($_COOKIE['selected_library'])) {
                $library = $tests->GetLibraryByID((int) $_COOKIE['selected_library']);

                if($library) {
                    if ($user) {
                        $current_progress = $progress->GetForUserByLibrary($user, $library);

                        $library_tasks = $library->GetTasks();
                        $completed_tasks = $current_progress->GetCompletedTasks();
                        $current_task = $completed_tasks < count($library_tasks) ? $library_tasks[$completed_tasks] : null;

                        $json_response["task"] = $current_task;
                    } else {
                        $json_response["status"] = $status_codes['NO_AUTH'];
                    }
                } else {
                    $json_response["status"] = $status_codes['INVALID_DATA'];
                }
            } else {
                $json_response["status"] = $status_codes['UNKNOWN_TEST'];
            }
        } else if($data['method'] == "check_output") {
            if(isset($data['runtime_output'])) {
                if (!empty($_COOKIE['selected_library'])) {
                    $library = $tests->GetLibraryByID((int)$_COOKIE['selected_library']);

                    if ($library) {
                        if ($user) {
                            $current_progress = $progress->GetForUserByLibrary($user, $library);

                            $library_tasks = $library->GetTasks();
                            $completed_tasks = $current_progress->GetCompletedTasks();
                            $current_task = $completed_tasks < count($library_tasks) ? $library_tasks[$completed_tasks] : null;

                            if(gettype($data['runtime_output']) == "array"){
                                $data['runtime_output'] = join("\n", $data['runtime_output']);
                            }

                            $runtime_output = trim($data['runtime_output']);
                            if(!$current_task) {
                                $json_response["status"] = $status_codes['UNKNOWN_TEST'];
                            } else if($runtime_output == trim(htmlspecialchars_decode($current_task->GetExpectedOutput()))) {
                                if(!$progress->AnswerCorrect($user, $library)) {
                                    $json_response["status"] = $status_codes['DB_ERROR'];
                                }
                            } else {
                                $json_response["status"] = $status_codes['WRONG_OUTPUT'];
                            }
                        } else {
                            $json_response["status"] = $status_codes['NO_AUTH'];
                        }
                    } else {
                        $json_response["status"] = $status_codes['INVALID_DATA'];
                    }
                } else {
                    $json_response["status"] = $status_codes['UNKNOWN_TEST'];
                }
            } else {
                $json_response["status"] = $status_codes['INVALID_DATA'];
            }
        } else {
            $json_response["status"] = $status_codes['INVALID_METHOD'];
        }
    } else {
        $json_response["status"] = $status_codes['INVALID_SCOPE'];
    }

}

echo json_encode($json_response, JSON_UNESCAPED_UNICODE);
