<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
   <link rel="stylesheet" href="Profile.css">
</head>

<body>

    <?php
    session_start();
    require_once '../Database/DBconnection.php';

    if (!isset($_SESSION['userid'])) {
        header('Location: login.php');
        exit();
    }
    $conn = new DBconnection();
    $connection = $conn->openConnection();
    $user = $conn->GetUsersDetails($connection, 'users', $_SESSION['userid']);

    if ($user->num_rows > 0) {
        $user = $user->fetch_assoc();
    } else {
        echo "User not found.";
        $conn->closeConnection($connection);
        exit(404);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $keyname = ['name', 'email', 'password'];
        $needToUpdate = [];

        foreach ($keyname as $key) {
            if (!empty(trim($_POST[$key]))) {
                $needToUpdate[$key] = trim($_POST[$key]);
            }
        }
        try {

            $conn = new DBconnection();
            $connection = $conn->openConnection();
            $exists = $conn->CheckExistingUserEmail($connection, 'users', $email);
            if (!$exists || $email == $user['email']) {
                if (array_key_exists('password', $needToUpdate)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $needToUpdate['password'] = $hashed_password;
                }
                $result = $conn->UpdateUserProfile($connection, 'users', $_SESSION['userid'], $needToUpdate);

                if ($result > 0) {
                    http_response_code(200);
                } else {
                    http_response_code(502);
                }
            } else {
                http_response_code(409);
            }
        } catch (Exception $e) {
            http_response_code(500);
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
        session_unset();
        $true =  session_destroy();
        setcookie('userid', '', time() - 3600, "/");
        setcookie('latitude', '', time() - 3600, "/");
        setcookie('longitude', '', time() - 3600, "/");
        setcookie('timezone', '', time() - 3600, "/");
        setcookie('location_label', '', time() - 3600, "/");

        if ($true) {
            http_response_code(200);
        } else {
            http_response_code(500);
        }
    }
    ?>

<div class="back-button">
    <button  onclick="window.location.href='./Dashboard.html'">
        << Back to Dashboard
    </button>
</div>

    <div class="profile-container">
        <h1>Profile</h1>

        <div class="profile-item">
            <label>Name:</label>
            <span><?php echo htmlspecialchars($user['name']); ?></span>
        </div>

        <div class="profile-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($user['email']); ?></span>
        </div>

        <div class="profile-item">
            <label>Timezone:</label>
            <span><?php echo htmlspecialchars($user['timezone']); ?></span>
        </div>

        <div class="button-group">
            <button class="edit-btn" onclick="openModal()">Edit Profile</button>
            <form action="" method="POST" onsubmit="Logout(event)">
                <button type="submit" name="logout" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Profile</h2>
            <form method="POST" onsubmit="UpdateProfile(event)">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Password (leave blank to keep current):</label>
                    <input type="password" name="password" placeholder="Enter new password">
                </div>

                <button type="submit" name="update_profile" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        function UpdateProfile(event) {
            event.preventDefault();

            const form = event.currentTarget;
            const fd = new FormData(form);

            const xhhtp = new XMLHttpRequest();
            xhhtp.open("POST", "Profile.php", true);
            xhhtp.onload = function() {
                if (this.status === 200) {
                    alert("Profile Updated Successfully");
                    location.reload();
                } else if (this.status === 409) {
                    alert("Email already exists. Please use a different email.");
                } else {
                    alert("Error updating profile.");
                }
            };
            xhhtp.send(fd);
        }

        function Logout(event) {
            event.preventDefault();

            const xhhtp = new XMLHttpRequest();
            xhhtp.open("POST", "Profile.php", true);
            xhhtp.onload = function() {
                if (this.status === 200) {
                    alert("Logged out successfully");
                    location.reload();
                } else {
                    alert("Error occur while logging out.");
                }
            };
            xhhtp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhhtp.send("logout=" + encodeURIComponent("true"));
        }
    </script>
</body>

</html>