(function(){
    let form = document.querySelector('form#taskEdit');
    let btnDelete = form.querySelector('button#task_delete');

    form.addEventListener('submit', e => {
        e.preventDefault();

        let submitBtn = form.querySelector('button[type="submit"]');

        SendAJAX('POST', '/ajax.php', {
            scope: 'forms',
            method: 'task_edit',
            action: 'save',
            task_id: form.dataset.taskId,
            new_title: form.querySelector('input#task_name').value,
            new_question: form.querySelector('input#task_question').value,
            new_expected_output: form.querySelector('textarea#task_output').value,
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

        if(prompt("Type \"delete\" for delete the task.") === "delete") {
            SendAJAX('POST', '/ajax.php', {
                scope: 'forms',
                method: 'task_edit',
                action: 'delete',
                task_id: btnDelete.closest('form').dataset.taskId,
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