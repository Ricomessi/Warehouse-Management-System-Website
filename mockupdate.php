    <?php
    session_start();
    include 'firebaseconfig.php';
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
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
    if (isset($_GET['username'])) {
        $id = $_GET['username'];
        $reference = $database->getReference('users/' . $id);
        $snapshot = $reference->getSnapshot();

        if ($snapshot->exists()) {
            $user = $snapshot->getValue();
        } else {
            echo "User not found!";
            exit();
        }
    } else {
        echo "No user ID specified!";
        exit();
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
                <form action="prosesupdateprofile.php" method="POST" enctype="multipart/form-data" class="firstForm">
                    <div class="details">
                        <label for="nama">Nama</label>
                        <input class="registration" type="text" name="nama" id="registerFullName" value="<?php echo htmlspecialchars($user['nama']); ?>">
                    </div>
                    <div class="details">
                        <label for="email">Email</label>
                        <input class="registration" type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="details">
                        <label for="password">Password</label>
                        <input class="registration" type="password" name="password" id="password" value="<?php echo htmlspecialchars($user['password']); ?>">
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