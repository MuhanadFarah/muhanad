<?php
session_start();

$phone = "+252907440616";
$password = "Hanandez@123";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputPhone = trim($_POST['phone']);
    $inputPassword = trim($_POST['password']);

    if ($inputPhone === $phone && $inputPassword === $password) {
        $_SESSION['manage_logged_in'] = true;
        header("Location: manage_dashboard.php");
        exit;
    } else {
        $error = "Invalid phone number or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: url('https://www.tibettravel.org/blog/wp-content/uploads/2014/04/Beautiful-Scenery-of-a-Mountain-Village-in-Tibet.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-box {
      background-color: rgba(0, 0, 0, 0.75);
      padding: 2rem 3rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
      width: 100%;
      max-width: 400px;
      color: white;
    }

    .login-box h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
      color: #00bfff;
    }

    .login-box input {
      width: 100%;
      padding: 0.7rem;
      margin-bottom: 1.2rem;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
    }

    .login-box button {
      width: 100%;
      padding: 0.8rem;
      background-color: #00bfff;
      border: none;
      color: white;
      font-size: 1rem;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .login-box button:hover {
      background-color: #007acc;
    }

    .error-message {
      color: #ff4d4d;
      text-align: center;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <form class="login-box" method="POST">
    <h2>Manage Dashboard Login</h2>
    
    <?php if ($error): ?>
      <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <input type="text" name="phone" placeholder="Phone number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</body>
</html>
