<?php

use LDAP\Result;

session_start();
require_once '../Database/DBconnection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST['email']) ?? '';
    $password = trim($_POST['password']) ?? '';

    $ERROR = [];
    $proceed = true;

    // Empty checks
    if (empty($email) || empty($password)) {
        http_response_code(422);
        $ERROR['emptyFieldsErr'] = "Email and password are required.";
        if (empty($email)) $ERROR['emailErr'] = "Email field is empty";
        if (empty($password)) $ERROR['passwordErr'] = "Password field is empty";
        echo json_encode($ERROR);
        exit;
    }

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ERROR['emailErr'] = "Invalid Email Address";
        $proceed = false;
    }

    if (strlen($password) < 8 || strlen($password) > 32) {
        $ERROR['passwordErr'] = "Password should be within 8 to 32 characters";
        $proceed = false;
    }

    if (!$proceed) {
        http_response_code(422);
        echo json_encode($ERROR);
        exit;
    }

    try {
        $db = new DBconnection();
        $connection = $db->openConnection();

        $result = $db->userLogin($connection, 'users', $email);

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['userid'] = $user['id'];
                $_SESSION['latitude'] = $user['latitude'];
                $_SESSION['longitude'] = $user['longitude'];
                $_SESSION['timezone'] = $user['timezone'];
                $_SESSION['location_label'] = $user['location_label'];

                if( isset($_POST['rememberMe']) ) {
                    // Set cookie for 30 days
                    setcookie('userid', $user['id'], time() + (30 * 24 * 60 * 60), "/");
                    setcookie('latitude', $user['latitude'], time() + (30 * 24 * 60 * 60), "/");
                    setcookie('longitude', $user['longitude'], time() + (30 * 24 * 60 * 60), "/");
                    setcookie('timezone', $user['timezone'], time() + (30 * 24 * 60 * 60), "/");
                    setcookie('location_label', $user['location_label'], time() + (30 * 24 * 60 * 60), "/");

                }else
                {
                    // Expire the cookie
                    setcookie('userid', '', time() - 3600, "/");
                    setcookie('latitude', '', time() - 3600, "/");
                    setcookie('longitude', '', time() - 3600, "/");
                    setcookie('timezone', '', time() - 3600, "/");
                    setcookie('location_label', '', time() - 3600, "/");
                }

                http_response_code(200);
                exit;
            } else {
                $ERROR['passwordErr'] = 'Password does not match';
                http_response_code(401);
                echo json_encode($ERROR);
                exit;
            }
        } else {
            http_response_code(401);
            $ERROR['emailErr'] = 'No email match';
            echo json_encode($ERROR);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo $e->getMessage();
        exit;
    }
}
