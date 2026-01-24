<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="Designs/credential.css">
    <script src="../api/JS/login.js"></script>
</head>

<body>
    <div class="formContainer">
        <h1>Salah Tracker</h1>
        <h3>Login</h3>
        <form onsubmit="CheckCredentials(event)">
            <input type="text" name="email" placeholder="Email">
            <span id="emailErr" style="color:red;"></span>
            <input type="password" name="password" placeholder="Password">
            <span id="passwordErr" style="color:red;"></span>
            <div>
                <label><input type="checkbox">Remember me</label>
                <label><a href="#">Forgot pasword?</a></label>
            </div>
            <input type="submit" name="btnlogin" value="Login">
            <span id="emptyFieldsErr" style="color:red;"></span>
            <label>Don't have an account? <a href="Registration.php">Create account</a></label>
        </form>
    </div>
    <script>

    </script>
</body>

</html>