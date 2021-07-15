document.querySelectorAll('a[href="#library_select"]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();

        SendAJAX('POST', '/ajax.php', {
            scope: 'progress',
            method: 'activate_library_tests',
            library_id: link.dataset.libraryId,
        }).then(data => {
            data = JSON.parse(data);
            let status_code = data.status[0];
            let status_msg = data.status[1];

            if(status_code === 0) {
                link.closest('li').classList.add('active');
                document.location.replace('/');
            } else {
                alert(status_msg);
            }
        });
    });
});
