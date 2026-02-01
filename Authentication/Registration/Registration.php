<!DOCTYPE html>
<html lang="en">
<?php
// session_start();

if (isset($_COOKIE['userid']) && !empty($_COOKIE['userid'])) {

    $_SESSION['userid'] = $_COOKIE['userid'];
    $_SESSION['latitude'] = $_COOKIE['latitude'];
    $_SESSION['longitude'] = $_COOKIE['longitude'];
    $_SESSION['timezone'] = $_COOKIE['timezone'];
    $_SESSION['location_label'] = $_COOKIE['location_label'];
    header("Location: ../../Home/Dashboard");
}

if( isset($_SESSION['userid']) && !empty($_SESSION['userid']) ) {
    header("Location: ../../Home/Dashboard");
    exit();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="../credential.css">
    <script src="../../api/JS/registration.js"></script>
</head>

<body>
    <div class="formContainer">
        <h1>Salah Tracker</h1>
        <h3>Registration</h3>

        <form onsubmit="CreateUser(event)">
            <input type="text" name="name" placeholder="Full Name">
            <span id="nameErr" style="color:red;"></span>

            <input type="email" name="email" placeholder="Email">
            <span id="emailErr" style="color:red;"></span>

            <input type="password" name="password" placeholder="Password">
            <span id="passwordErr" style="color:red;"></span>

            <!-- Display dropdown (optional) -->
            <select name="timezone_display" id="timezone_display">
                <option selected hidden value="">Time zone (optional)</option>
                <option value="Asia/Dhaka">(UTC+06:00) Dhaka, Bangladesh</option>
                <option value="Asia/Karachi">(UTC+05:00) Karachi, Pakistan</option>
                <option value="Asia/Kolkata">(UTC+05:30) India (Kolkata)</option>
                <option value="Asia/Riyadh">(UTC+03:00) Riyadh, Saudi Arabia</option>
                <option value="Asia/Dubai">(UTC+04:00) Dubai, UAE</option>
                <option value="Asia/Jakarta">(UTC+07:00) Jakarta, Indonesia</option>
                <option value="Asia/Kuala_Lumpur">(UTC+08:00) Kuala Lumpur, Malaysia</option>
                <option value="Asia/Istanbul">(UTC+03:00) Istanbul, Turkey</option>
                <option value="Africa/Cairo">(UTC+02:00) Cairo, Egypt</option>
                <option value="Europe/London">(UTC+00:00) London, UK</option>
                <option value="America/New_York">(UTC-05:00) New York, USA</option>
                <option value="America/Chicago">(UTC-06:00) Chicago, USA</option>
                <option value="America/Denver">(UTC-07:00) Denver, USA</option>
                <option value="America/Los_Angeles">(UTC-08:00) Los Angeles, USA</option>
                <option value="Australia/Sydney">(UTC+10:00) Sydney, Australia</option>
                <option value="UTC">(UTC+00:00) UTC</option>
            </select>

            
            <input type="hidden" name="timezone" id="timezone" value="">
            <span id="timezoneErr" style="color:red;"></span>

            
            <button id="btnUseLocation" type="button">Use my location</button>
            <small id="locationStatus" style="display:block;margin:6px 0;color:#444;"></small>

            <input type="hidden" name="lat" id="lat" value="">
            <input type="hidden" name="lng" id="lng" value="">
            <input type="text" name="location_label" id="location_label" placeholder="Location (auto)" readonly>
            <span id="locationErr" style="color:red;"></span>

            <input type="submit" name="btnRegister" value="Register">
            <span id="emptyFieldsErr" style="color:red;"></span>

            <label>Already have an account? <a href="../Login/">Login</a></label>
        </form>
    </div>
</body>

</html>