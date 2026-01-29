<?php

require_once '../Database/DBconnection.php';

if ($_SERVER["REQUEST_METHOD"] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $userPass = (string)($_POST['password'] ?? '');

    // now optional (but recommended)
    $timezone = trim($_POST['timezone'] ?? '');

    // optional location fields
    $lat = trim($_POST['lat'] ?? '');
    $lng = trim($_POST['lng'] ?? '');
    $locationLabel = trim($_POST['location_label'] ?? '');

    $ERROR = [];
    $proceed = true;

    // Required fields
    if (empty($name) || empty($email) || empty($userPass)) {
        http_response_code(422);

        $ERROR['emptyFieldsErr'] = "Name, email and password are required.";
        if (empty($name)) $ERROR['nameErr'] = "Name Field is empty";
        if (empty($email)) $ERROR['emailErr'] = "Email Field is empty";
        if (empty($userPass)) $ERROR['passwordErr'] = "Password Field is empty";

        echo json_encode($ERROR);
        exit;
    }

    // If user didn't provide timezone AND didn't provide full coordinates, block (recommended)
    if ($timezone === '' && ($lat === '' || $lng === '')) {
        $ERROR['timezoneErr'] = 'Select a timezone or click "Use my location".';
        $proceed = false;
    }

    // Name validation: letters/spaces only + length
    if (!preg_match("/^[a-zA-Z ]+$/", $name) || strlen($name) < 2 || strlen($name) > 50) {
        $ERROR['nameErr'] = "Invalid Name Format (2 to 50 characters, a-z and spaces)";
        $proceed = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ERROR['emailErr'] = "Invalid Email Address";
        $proceed = false;
    }

    // Password validation
    if (strlen($userPass) < 8 || strlen($userPass) > 32) {
        $ERROR['passwordErr'] = "Password Should be within 8 to 32 characters";
        $proceed = false;
    }

    // Timezone validation (only if provided)
    if ($timezone !== '' && !in_array($timezone, timezone_identifiers_list(), true)) {
        $ERROR['timezoneErr'] = "Invalid timezone value";
        $proceed = false;
    }

    // Location validation (optional, but if one exists, both must exist)
    if (($lat !== '' && $lng === '') || ($lat === '' && $lng !== '')) {
        $ERROR['locationErr'] = "Latitude and Longitude must both be provided.";
        $proceed = false;
    }

    if ($lat !== '' && $lng !== '') {
        if (!is_numeric($lat) || (float)$lat < -90 || (float)$lat > 90) {
            $ERROR['locationErr'] = "Invalid latitude.";
            $proceed = false;
        }
        if (!is_numeric($lng) || (float)$lng < -180 || (float)$lng > 180) {
            $ERROR['locationErr'] = "Invalid longitude.";
            $proceed = false;
        }
    }

    if (!$proceed) {
        http_response_code(422);
        echo json_encode($ERROR);
        exit;
    }

    try {
        $db = new DBconnection();
        $connection = $db->openConnection();

        $hash = password_hash($userPass, PASSWORD_DEFAULT);

        $result = $db->userRegistration(
            $connection,
            'users',
            $name,
            $email,
            $hash,
            $timezone,       // can be ''
            $lat,            // can be ''
            $lng,            // can be ''
            $locationLabel   // can be ''
        );

        if ($result) {
            http_response_code(201);
            exit;
        }

        http_response_code(500);
        exit;

    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
        http_response_code(500);
        exit;
    }
}
