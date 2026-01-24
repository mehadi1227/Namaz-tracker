<?php

require_once '../Database/DBconnection.php';
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $userPass = $_POST['password'] ?? '';
    $timezone = $_POST['timezone'] ?? '';

    $ERROR = [];
    $procced = true;
    if (
        empty($name) ||
        empty($email) ||
        empty($userPass) ||
        empty($timezone)
    ) {
        http_response_code(422);
        $ERROR['emptyFieldsErr'] = "All fields are required.";
        $ERROR['nameErr'] = "Name Field is empty";
        $ERROR['emailErr'] = "Email Field is empty";
        $ERROR['passwordErr'] = "Password Field is empty";
        $ERROR['timezoneErr'] = "Time Zone Must be selected";
        echo json_encode($ERROR);
        exit;
    } else {

        if (!preg_match("/^[a-zA-Z ]+$/", $name) || strlen($userPass) < 2 || strlen($userPass) > 32) {

            $ERROR['nameErr'] = "Invalid Name Format (2 to 32 characters, a-z)";
            $procced = false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $ERROR['emailErr'] = "Invalid Email Address";
            $procced = false;
        }

        if (strlen($userPass) < 8 || strlen($userPass) > 32) {

            $ERROR['passwordErr'] = "Password Should be within 8 to 32 characters";
            $procced = false;
        }

        if ($procced) {
            try {

                $db = new DBconnection();
                $connection = $db->openConnection();

                $hash = password_hash($userPass, PASSWORD_DEFAULT);
                $result = $db->userRegistration($connection, 'users', $name, $email, $hash, $timezone);

                if ($result) {
                    http_response_code(201);
                    exit;
                } else {
                    http_response_code(500);
                    exit;
                }
            } catch (Exception $e) {
                echo "Database error: " . $e->getMessage();
                http_response_code(500);
                exit;
            }
        } else {
            echo json_encode($ERROR);
            http_response_code(422);
            exit;
        }
    }
}
