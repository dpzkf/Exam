function SendAJAX(method, url, data) {
    return new Promise((resolve, reject) => {
        if(typeof(data) == "object") {
            data = JSON.stringify(data);
        }

        let xhr = new XMLHttpRequest();

        xhr.open(method, url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(data);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                resolve(xhr.responseText);
            }
        }
    });
}
