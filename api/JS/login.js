const CheckCredentials = (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const fd = new FormData(form);

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {

        if (this.readyState === 4 && this.status === 200) {
            window.location.href = '../../Home/Dashboard/';
        }
        else if (this.readyState === 4 && (this.status === 422 || this.status === 401)) {
            const errors = JSON.parse(this.responseText);
            ErrorShowing(errors);
        }
        else if (this.readyState === 4 && this.status === 500) {
            alert('Server Error: ' + this.responseText);
        }
    }

    xhttp.open('post', '../../api/login.php', true);
    xhttp.send(fd);

}

const ErrorShowing = (errors) => {

    document.getElementById('emptyFieldsErr').textContent = errors.emptyFieldsErr ?? '';

    document.getElementById('emailErr').textContent = errors.emailErr ?? '';
    document.getElementById('passwordErr').textContent = errors.passwordErr ?? '';

}