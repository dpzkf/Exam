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
if(!empty($_COOKIE['security_key']) && $secauthkey = $secauth->SearchKey($_COOKIE['security_key'])) {
    $user = $auth->SearchUserByID($secauthkey->GetUserID());
    // $user_email = $user->GetEmail();

    if($user->IsTeacher()) {
        $upper_menu_items[] = ["Teacher panel", "teacher.php", "link"];
    }
    $upper_menu_items[] = ["Progress", "#", "link"];
    $upper_menu_items[] = ["Logout", "#logout", "link"];
} else {
    $upper_menu_items[] = ["Login", "/login.html", "link guest"];
    $upper_menu_items[] = ["Register", "/register.html", "link guest"];
}

$upper_menu = build_menu_upper_links($upper_menu_items);

?>

<!DOCTYPE html>
    <html>
    <head>
        <title>Compile C#</title>
        <style>
            body
            {
                font-family: Arial;
                background-color: #1a1a1a;
                color: #929292;
                /*background-image: linear-gradient(to bottom, #2c2c2c, #1a1a1a 116px);*/
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
        </style>

        <script src="https://unpkg.com/monaco-editor@latest/min/vs/loader.js"></script>
        <script>
            require.config({ paths: { vs: 'https://unpkg.com/monaco-editor@latest/min/vs' } });

            window.MonacoEnvironment = { getWorkerUrl: () => proxy };
            let proxy = URL.createObjectURL(new Blob([`
                self.MonacoEnvironment = {
                    baseUrl: 'https://unpkg.com/monaco-editor@latest/min/'
                };
                importScripts('https://unpkg.com/monaco-editor@latest/min/vs/base/worker/workerMain.js');
            `], { type: 'text/javascript' }));

            let startExample = `using System;
        using System.Collections.Generic;
        using System.Linq;
        using System.Text.RegularExpressions;

        namespace TestProject
        {
            public class Program
            {
                public static void Main(string[] args)
                {
                    //Your code goes here
                    Console.WriteLine("Hello, world!");
                }
            }
        }`;

            require(['vs/editor/editor.main'], function () {
                window.editor = monaco.editor.create(document.getElementById('container'), {
                    value: startExample,
                    language: 'csharp',
                    theme: 'vs-dark'
                });

                console.log('monaco languages', monaco.languages.getLanguages());
            });
        </script>
    </head>

    <body>
        <table style="width: 100%; padding-right: 2em" id="upper_menu">
            <tr>
                <td>
                    <span style="float: right;"><?=$upper_menu;?></span>
                </td>
            </tr>
        </table>

        <table style="table-layout:fixed;width:100%;">
            <tr>
                <td width="100%" valign="top"></td>


            <h2>
                Language: C#

            </h2>


            </tr>
        </table>


        <div class="formcontent" style="padding-top: 0.5em; margin-bottom: 2em;">
            <table style="width: 95%; margin:0;">
                <tr>
                    <td>
                        <!--<span style="margin: 0.5em 0.5em 0 0">Task 1: Display “Hello, world!”</span> -->
                    </td>
                </tr>
            </table>
        </div>

        <div style="display:flex;">
            <div style="width: 75%; margin-top:1em;">
                <div id="container"></div>
            </div>
            <div style="max-height: inherit;width: 100%;margin-top: 1em;padding: 0 1em; flex-basis: 30%">
                <textarea class="console-output"></textarea>
            </div>
        </div>

        <table style="width: 95%; margin-top:0.5em;">
            <tr>
                <td align="left">
                    <input id="Run" type="button" value="Run It"/>

                </td>
            </tr>
        </table>

        <script>
            function objectIsValid(...obj){
                return obj.every(e => typeof(e) != 'undefined' && e != null);
            }

            function structIsValid(approx_struct, obj){
                for(let key in approx_struct){
                    if(!objectIsValid(obj[key]) || typeof(approx_struct[key]) == "object" && !structIsValid(approx_struct[key], obj[key])) return false;

                    if(typeof(approx_struct[key]) == "string" && approx_struct[key].split('/').filter(type => {
                        type = type.trim();
                        return type.length > 0 && type === typeof(obj[key]);
                    }).length <= 0) return false; // type mismatch
                }

                return true;
            }

            let btn_run = document.querySelector('#Run');
            btn_run.addEventListener('click', e => {
                let xhr = new XMLHttpRequest();
                xhr.open('POST', 'compile.php');

                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = () => {
                    if(xhr.readyState !== xhr.DONE) return;

                    let outputConsole = document.querySelector('.console-output');

                    let response = JSON.parse(xhr.response);
                    console.log(xhr.response);
                    console.log(response);

                    if(structIsValid({compiler: {exit_code: 'number', output: 'object'}}, response)) {
                        // compiler processing
                        outputConsole.value = response.compiler.output.join("\n");
                        console.log(response.compiler.output.join("\n"));
                        if(response.compiler.exit_code === 0) {
                            monaco.editor.setModelMarkers(editor.getModel(), "owner", null);
                            if(structIsValid({runtime: {exit_code: 'number', output: 'object'}}, response)) {
                                // runtime processing
                                outputConsole.value = response.runtime.output.join("\n");
                            }
                        } else {
                            // compiler errors processing
                            console.log(response.compiler.output);

                            let reg = /^.*\((\d*),(\d*)\): (.*$)/gm;
                            let errors = response.compiler.output.map(e => {
                                // sample error: "./storage/code/886266cc62a20a8.cs(13,36): error CS1525: Unexpected symbol `1'"
                                let reg = /^.*\((\d*),(\d*)\): (.*$)/gm; // line, column and error text

                                return reg.exec(e);
                            }).filter(e => e != null);

                            let modelMarkers = errors.map(e => {
                                let startLineNumber = parseInt(e[1]);
                                let startColumn = parseInt(e[2]);
                                let endLineNumber = startLineNumber+1;
                                let endColumn = 1;

                                return {
                                    startLineNumber,
                                    startColumn,
                                    endLineNumber,
                                    endColumn,
                                    message: e[3],
                                    severity: monaco.MarkerSeverity.Error
                                };
                            });

                            console.log('model markers', modelMarkers);

                            monaco.editor.setModelMarkers(editor.getModel(), "owner", modelMarkers);
                        }
                    }
                };

                xhr.send('code=' + encodeURIComponent(editor.getValue()));
            });
        </script>

        <script src="/js/jecookie.js"></script>
        <script src="/js/app.js"></script>
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
