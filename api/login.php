<?php
require_once '../Database/DBconnection.php';
if (isset($_POST['btnlogin'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(empty($email)|| empty($password))
    {
        echo 'ALL field required';
        exit;
    }
    else{

        try{
            $db = new DBconnection();

            

            $query = "SELECT userid, password FROM users 
                      WHERE email='$email';";
            $result = $db->DQlQuery($query);
            
            var_dump($result);
            if(password_verify($password,$result[0]['password']))
            {
                // echo 'login';
                $_SESSION['userid'] = $result[0]['userid'];
                header("Location: ../Pages/Dashboard.html");
                exit();
            }
        }catch(PDOException $e)
        {
            echo "Database error: " . $e->getMessage();
        }
    }
}
