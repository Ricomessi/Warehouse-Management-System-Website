<?php
session_start();
include 'firebaseconfig.php';

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

// Clear session messages
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : array();
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
unset($_SESSION['errors']);
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ced4da;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            background-color: #fff;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 30px;
        }
        .form-label,
        .form-control {
            color: #495057;
        }
        .form-control {
            background-color: #ffffff;
            border: 1px solid #ced4da;
        }
        .form-control::placeholder {
            color: #adb5bd;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary,
        .btn-secondary {
            width: 45%;
            margin-top: 10px;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            margin-right: 180%;
        }
        .btn-primary {
            background-color: #0D6EFD;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #0c50b7;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        img {
            margin-top: 10px;
            border-radius: 5px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 100px;
            height: auto;
        }
        .mb-3 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Profile</h1>
        <?php if (!empty($errors)) : ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="alert alert-success">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        <form action="prosesUpdateProfile.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($id); ?>">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama:</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
            </div>
            <div class="mb-3">
                <label for="profile" class="form-label">Profile Picture:</label>
                <input type="file" class="form-control" id="profile" name="profile">
                <?php if (!empty($user['profile'])) : ?>
                    <img src="<?php echo htmlspecialchars($user['profile']); ?>" alt="Profile Picture">
                <?php endif; ?>
            </div>
            <a href="mainStaff.php" class="btn btn-secondary">Back</a>
            <button type="submit" name="updateProfile" class="btn btn-primary" style="margin-left: 130px;">Update</button>
        </form>
    </div>
</body>
</html>
