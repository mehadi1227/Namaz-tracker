<?php

require_once '../Database/DBconnection.php';
if (isset($_POST['btnRegister'])) {
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $userPass = $_POST['password'] ?? '';
    $timezone = $_POST['timezone'] ?? '';

    if (
        empty($name) ||
        empty($email) ||
        empty($userPass) ||
        empty($timezone)
    ) {
        echo "All fields are required.";
        exit;
    } else {


        try {

            $db = new DBconnection();
            $useridList = $db->DQlQuery("SELECT userid
                                         FROM `users`
                                         ORDER BY userid DESC;");
            if (!empty($useridList)) {
                $lastID = $useridList[0]['userid'];
                $num = (int)substr($lastID, 2);
                $nextNum = $num + 1;
            } else {

                $nextNum = 1;
            }

            $newUserId = 'u-' . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);

            $hash = password_hash($userPass, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (userid,name, email, password, timezone)
                      VALUES ('$newUserId','$name', '$email', '$hash', '$timezone')";
            $retun = $db->DmlQuery($query);

            echo $retun;
            echo "Registration successful!";
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
}
