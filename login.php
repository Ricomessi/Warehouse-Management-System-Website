<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="gradienHabibi">

    <?php

    $errorsu = array();
    if (isset($_SESSION['errorsu'])) {
        $errorsu = $_SESSION['errorsu'];
        unset($_SESSION['errorsu']);
    }
    ?>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row border rounded-5 p-3 bg-white shadow box-area">

            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                <div class="featured-image mb-3">
                    <img src="img/logo.png" class="img-fluid">
                </div>
            </div>

            <div class="col-md-6 right-box">
                <div class="row align-items-center">

                    <div class="header-text mb-4">
                        <h2>Selamat Datang!</h2>
                        <p>Kami senang melihat Anda kembali.</p>
                    </div>

                    <form id="login-form" action="auth.php" method="POST" class="w-100">
                        <div class="form-group">
                            <label for="loginUsername">Username</label>
                            <input type="text" class="form-control" id="loginUsername" name="loginUsername" placeholder="Enter username" value="<?php echo isset($username) ? $username : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Password" required>
                        </div>
                        <div class="g-recaptcha" data-sitekey="6LeCJx4pAAAAAEzdenUgP0ecNBAnCLPdlXleEwrt"></div>
                        <button type="submit" class="btn btn-primary btn-lg fs-6 w-100" name="loginSubmit">Login</button>
                        <?php if (!empty($errorsu)) : ?>
                            <div class="alert alert-danger mt-3">
                                <?php foreach ($errorsu as $error) : ?>
                                    <p><?php echo $error; ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</body>

</html>