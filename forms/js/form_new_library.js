(function(){
    let form = document.querySelector('form#libraryNew');

    form.addEventListener('submit', e => {
        e.preventDefault();

        let submitBtn = form.querySelector('button[type="submit"]');

        SendAJAX('POST', '/ajax.php', {
            scope: 'forms',
            method: 'library_new',
            action: 'save',
            library_name: form.querySelector('input#library_name').value,
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

    RebindSideMenuEvents();
})();