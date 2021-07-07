function RebindSideMenuEvents() {
    document.querySelectorAll('a[href="#library_select"]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();

            SendAJAX('POST', '/ajax.php', {
                scope: 'forms',
                method: 'library_edit',
                library_id: link.dataset.libraryId,
            }).then(data => {
                data = JSON.parse(data);
                let status_code = data.status[0];
                let status_msg = data.status[1];

                if(status_code === 0) {
                    mainContent.innerHTML = data.form;

                    if(data.js) {
                        let script = document.createElement('script');
                        script.text = data.js;
                        mainContent.appendChild(script);
                    }
                }else {
                    alert(status_msg);
                }
            });
        });
    });

    document.querySelectorAll('a[href="#task_edit"]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();

            SendAJAX('POST', '/ajax.php', {
                scope: 'forms',
                method: 'task_edit',
                task_id: btn.dataset.taskId,
            }).then(data => {
                data = JSON.parse(data);
                let status_code = data.status[0];
                let status_msg = data.status[1];

                if (status_code === 0) {
                    mainContent.innerHTML = data.form;

                    if(data.js) {
                        let script = document.createElement('script');
                        script.text = data.js;
                        mainContent.appendChild(script);
                    }
                } else {
                    alert(status_msg);
                }
            });
        });
    });
}

function UpdateSideMenu() {
    SendAJAX('POST', '/ajax.php', {
        scope: 'forms',
        method: 'side_menu'
    }).then(data => {
        data = JSON.parse(data);
        let status_code = data.status[0];
        let status_msg = data.status[1];

        if(data.form) {
            document.querySelectorAll('.left-ul-menu > li:not(:first-child)').forEach(e => {
                e.remove();
            });

            document.querySelector('.left-ul-menu').innerHTML += data.form;
        }

        RebindSideMenuEvents();
    });
}

RebindSideMenuEvents();
