<?php

require_once 'autoload.php';

$auth = new \Components\Auth\Auth($mysqli, $salt['password'], $salt['sec_auth']);
$secauth = new \Components\Auth\SecAuth($mysqli);

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
    // $user_email = $user->GetEmail();

    if($user->IsTeacher()) {
        $upper_menu_items[] = ["Logout", "#logout", "link"];

        $tests = new \Components\Tests\Tests($mysqli);
        $side_menu_items = $tests->GetLibraries();
    } else {
        header('Location: /');
        exit();
    }
} else {
    header('Location: /');
    exit();
}

$upper_menu = build_menu_upper_links($upper_menu_items);
$side_menu = (new CGSideMenu($side_menu_items))->GenerateHTML();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher panel</title>
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

        ul.left-ul-menu {
            list-style: none;
            padding-left: 26px;
            color: #fff;
        }

        ul a {
            color: #fff;
            text-decoration: none;
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

        .side-menu {
            width: 240px;
            height: var(--main-content-max-height);
            background: #333333;
        }

        .full-width {
            width: 100%;
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

<div class="main-content flex-container">
    <div class="side-menu flex-no-shrink">
        <ul class="left-ul-menu">
            <li>
                <a href="#new_library">
                    <img src="/images/mdi_plus.svg" alt="Associative icon" class="va-middle"><span class="va-middle">Create new library</span>
                </a>
            </li>
            <?=$side_menu;?>
        </ul>
    </div>
    <div id="main-content" class="flex-container full-width flex-center">
        <span>Select action on left panel</span>
    </div>
</div>

<script src="/js/jecookie.js"></script>
<script src="/js/app.js"></script>
<script src="/js/app.teacher.js"></script>
<script>
    let security_key = new jecookie("security_key");
    if(security_key.load()) {
        let btnLogout = document.querySelector('a[href="#logout"]');
        if (btnLogout) {
            btnLogout.addEventListener('click', () => {
                SendAJAX('POST', '/ajax.php', JSON.stringify({
                    scope: 'auth',
                    method: 'logout',
                    security_key: security_key.data,
                })).then(data => {
                    data = JSON.parse(data);
                    let status_code = data.status[0];
                    let status_msg = data.status[1];

                    if(status_code === 0) {
                        document.location.replace('/');
                    }
                });
            });
        }
    }

    // TODO click events
    let mainContent = document.querySelector('#main-content');
    document.querySelector('a[href="#new_library"]').addEventListener('click', e => {
        e.preventDefault();

        SendAJAX('POST', '/ajax.php', {
            scope: 'forms',
            method: 'library_new'
        }).then(data => {
            data = JSON.parse(data);
            let status_code = data.status[0];
            let status_msg = data.status[1];

            if(status_code === 0) {
                mainContent.innerHTML = data.form;
            }else {
                alert(status_msg);
            }
        });
    });

    // let security_key = new jecookie("security_key");
    // if(security_key.load()) {
    //     SendAJAX('POST', '/ajax.php', JSON.stringify({
    //         scope: 'auth',
    //         method: 'is_valid',
    //         security_key: security_key.data,
    //     })).then(data => {
    //         data = JSON.parse(data);
    //         let status_code = data.status[0];
    //         let status_msg = data.status[1];
    //
    //         if(status_code !== 0) {
    //             document.location.replace('/');
    //         }
    //     });
    // }
</script>
</body>
</html>

