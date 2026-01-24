const CreateUser = (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const fd = new FormData(form);

    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {

        if (this.readyState === 4 && this.status === 201) {
            alert('User created successfully');
            window.location.href = './Login.php';
        }
        else if (this.readyState === 4 && this.status === 422) {
            const errors = JSON.parse(this.responseText);
            ErrorShowing(errors);
        }
        else if (this.readyState === 4 && this.status === 500 && (this.responseText.includes("Duplicate entry") && this.responseText.includes("for key 'email'"))) {
            //alert('server is down: ' + this.responseText);
            // document.getElementById('emailErr').textContent = '';
            ErrorShowing({emailErr : 'This email already exits'})
        }
        else if (this.readyState === 4 && this.status === 500) {
            alert('server is down: ' + this.responseText);
        }
    }

    xhttp.open('post', '../api/registration.php', true);
    xhttp.send(fd);

}

const ErrorShowing = (errors) => {

    document.getElementById('emptyFieldsErr').textContent = errors.emptyFieldsErr ?? '';
    document.getElementById('nameErr').textContent = errors.nameErr ?? '';
    document.getElementById('emailErr').textContent = errors.emailErr ?? '';
    document.getElementById('passwordErr').textContent = errors.passwordErr ?? '';
    document.getElementById('timezoneErr').textContent = errors.timezoneErr ?? '';
}