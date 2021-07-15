<?php

require_once 'autoload.php';

$auth = new \Components\Auth\Auth($mysqli, $salt['password'], $salt['sec_auth']);
$secauth = new \Components\Auth\SecAuth($mysqli);
$tests = new \Components\Tests\Tests($mysqli);
$progress = new \Components\Progress\Progress($mysqli, $tests);

// front-end features
function build_menu_upper_links($links_array) {
    $result = '';
    foreach ($links_array as $link) {
        $result .= '<a class="'.$link[2].'" href="'.$link[1].'">'.$link[0].'</a>';
    }

    return $result;
}

$upper_menu_items = [];
$side_menu_items = [];
if(!empty($_COOKIE['security_key']) && $secauthkey = $secauth->SearchKey($_COOKIE['security_key'])) {
    $user = $auth->SearchUserByID($secauthkey->GetUserID());
    // $user_email = $user->Get

    if($user !== null) {
        if($user->IsTeacher()) {
            $upper_menu_items[] = ["Teacher panel", "teacher.php", "link"];
        }

        $upper_menu_items[] = ["Home", "/index.php", "link"];
        $upper_menu_items[] = ["Logout", "#logout", "link"];
    } else {
        header('Location: /');
        exit();
    }
} else {
    header('Location: /');
    exit();
}

$upper_menu = build_menu_upper_links($upper_menu_items);
$side_menu = (new CGSideMenuProgress($progress, $tests, $user))->GenerateHTML();

$progress_ul = (new CGProgressList($progress->GetForUser($user)))->GenerateHTML();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress</title>

    <style>

        :root {
            --main-content-max-height: calc(100vh - 32px);
        }

        body
        {
            font-family: Arial;
            background-color: #1a1a1a;
            color: #929292;
            /*background-image: linear-gradient(to bottom, #2c2c2c, #1a1a1a 116px);*/
            margin: 0;
        }
        h2
        {
            color:Gray;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
        }
        textarea
        {
            color: #8f8f8f;
            background-color:#1a1a1a;
        }
        input[type=button],
        input[type=submit]
        {
            color: #8f8f8f;
            background-color:#1a1a1a;
            border: 1px solid #555;
            font-weight: bold;
        }
        input
        {
            color: #afafaf;
            background-color:#2a2a2a;
        }

        a.link
        {
            color: gray;
        }

        #container
        {
            height: 500px;
        }

        .console-output
        {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }

        table#upper_menu td a.link + a.link {
            margin-left: 22px;
        }

        .va-middle {
            vertical-align: middle;
        }

        /* ul.left-ul-menu {
            list-style: none;
            padding-left: 26px;
            color: #fff;
        } */

        /*ul a {
            color: #fff;
            text-decoration: none;
        }*/

        /* ul.left-ul-menu li a > img + span {*/
        /*    margin-left: 3px;*/
        /*}*/

        /*ul.progress-menu ul.library-items {*/
        /*    list-style: none;*/
        /*    margin-left: 3px;*/
        /*}*/

        ul a {
            color: #fff;
            text-decoration: none;
        }

        ul.left-ul-menu {
            list-style: none;
            padding-left: 26px;
            color: #fff;
        }

        ul.left-ul-menu.progress li a {
            color: #959595;
        }

        ul.left-ul-menu.progress li.active a {
            color: #fff;
        }

        ul.left-ul-menu li a > img + span {
            margin-left: 3px;
        }

        ul.left-ul-menu ul.library-items {
            list-style: none;
            margin-left: 3px;
        }

        .flex-container {
            display: flex;
        }

        .flex-no-shrink {
            flex-shrink: 0;
        }

        .flex-center {
            justify-content: center;
            align-items: center;
        }

        .main-content {
            max-height: var(--main-content-max-height);
            overflow: auto;
        }

        .full-height {
            height: var(--main-content-max-height);
        }

         .side-menu {
            width: 240px;
            height: var(--main-content-max-height);
            background: #333333;
        }
        .full-width {
            width: 100%;
        }

        .progress-menu ul {
            font-family: 'Roboto', sans-serif;
            font-size: 36px;
            color: #fff;
            list-style: none;
            margin: 3px;
            padding: 0;
        }

        .progress-menu ul li .steps {
            margin-left: 14px;
            color: #9d9d9d;
        }

        .progress-menu ul li+li::before {
            content: ' ';
            width: 66.6%;
            height: 1px;
            background: #c4c4c4;
            position: absolute;
            left: 50%;
            transform: translateX(-50%) translateY(-2px);
        }

        .progress-menu ul li+li {
            margin: 5px auto;
        }
    </style>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body>
<table style="width: 100%; padding-right: 2em; background: #333; padding: 4px 10px;" id="upper_menu">
    <tr>
        <td>
            <span style="float: right;"><?=$upper_menu;?></span>
        </td>
    </tr>
</table>

<div class="main-content full-height flex-center flex-container">
    <div class="side-menu flex-no-shrink">
        <ul class="left-ul-menu progress">
            <?=$side_menu;?>
        </ul>
    </div>
    <div id="main-content" class="progress-menu flex-container full-width flex-center">
        <?=$progress_ul;?>
    </div>
    <!--<div class="progress-menu flex-no-shrink">
        <?/*=$progress_ul;*/?>
    </div>-->
</div>

<script src="/js/jecookie.js"></script>
<script src="js/app.js"></script>
<script src="js/app.progress.js"></script>
</body>
</html>