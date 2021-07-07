(function(){
    let form = document.querySelector('form#libraryEdit');
    let btnDelete = document.querySelector('form#libraryEdit button#library_delete');

    form.addEventListener('submit', e => {
        e.preventDefault();

        let submitBtn = form.querySelector('button[type="submit"]');

        SendAJAX('POST', '/ajax.php', {
            scope: 'forms',
            method: 'library_edit',
            action: 'save',
            library_id: form.dataset.libraryId,
            new_title: form.querySelector('input#library_name').value,
        }).then(data => {
            data = JSON.parse(data);
            let status_code = data.status[0];
            let status_msg = data.status[1];

            if (status_code === 0) {
                UpdateSideMenu();

                submitBtn.style.background = "var(--color-success)";
                setTimeout(() => {
                    submitBtn.style.background = "";
                }, 750);
            } else {
                alert(status_msg);
            }
        });
    });

    btnDelete.addEventListener('click', e => {
        e.preventDefault();

        if(prompt("Type \"delete\" for delete the library.") === "delete") {
            SendAJAX('POST', '/ajax.php', {
                scope: 'forms',
                method: 'library_edit',
                action: 'delete',
                library_id: form.dataset.libraryId,
            }).then(data => {
                data = JSON.parse(data);
                let status_code = data.status[0];
                let status_msg = data.status[1];

                if (status_code === 0) {
                    document.location.reload();
                } else {
                    alert(status_msg);
                }
            });
        }
    });

    document.querySelectorAll('a[href="#task_delete"]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();

            if(prompt("Type \"delete\" for delete the task.") === "delete") {
                SendAJAX('POST', '/ajax.php', {
                    scope: 'forms',
                    method: 'task_edit',
                    action: 'delete',
                    task_id: btn.dataset.taskId,
                }).then(data => {
                    data = JSON.parse(data);
                    let status_code = data.status[0];
                    let status_msg = data.status[1];

                    if (status_code === 0) {
                        document.location.reload();
                    } else {
                        alert(status_msg);
                    }
                });
            }
        });
    });

    RebindSideMenuEvents();
})();