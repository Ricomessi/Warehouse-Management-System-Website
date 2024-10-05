<?php
session_start();
$errors = array();
$errorsu = array();
$success = "";

if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
} else if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
} else if (isset($_SESSION['errorsu'])) {
    $errorsu = $_SESSION['errorsu'];
    unset($_SESSION['errorsu']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Staff</title>
    <link rel="stylesheet" href="css/registerStaff.css">
</head>

<body class="gradienHabibi">
    <div class="container">
        <div class="register">
            <h1>Register</h1>
            <div class="line gradienHabibi"></div>
            <form action="auth.php" method="POST" enctype="multipart/form-data" class="firstForm">
                <div class="details">
                    <label for="registerFullName">Full Name</label>
                    <input class="registration" type="text" name="registerFullName" id="registerFullName" placeholder="Enter your name" required>
                </div>
                <div class="details">
                    <label for="registerEmail">E-mail</label>
                    <input class="registration" type="email" name="registerEmail" id="registerEmail" placeholder="Enter your Email" required>
                </div>
                <div class="details">
                    <label for="registerUsername">Username</label>
                    <input class="registration" type="text" name="registerUsername" id="registerUsername" placeholder="Enter your username" required>
                </div>
                <div class="details">
                    <label for="registerPassword">Password</label>
                    <input class="registration" type="password" name="registerPassword" id="registerPassword" placeholder="Enter your password" required>
                </div>
                <div class="details">
                    <label for="registerPasswordConfirmation">Confirm Password</label>
                    <input class="registration" type="password" name="registerPasswordConfirmation" id="registerPasswordConfirmation" placeholder="Confirm your password" required>
                </div>
                <div class="details">
                    <label for="profile">Profile Picture</label>
                    <input class="registration" type="file" id="profile" name="profile" accept="image/*">
                </div>
                <div class="button-container">
                    <a href="menuAdmin.php" class="btn-back">Back</a>
                </div>
                <div class="button-container">
                    <button type="submit" name="registerSubmit">Submit</button>
                </div>
            </form>
            <?php if (!empty($errors)) : ?>
                <div class="text-danger">
                    <h4>Registration Error Messages:</h4>
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($success)) : ?>
                <div class="text-success">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>