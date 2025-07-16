<?php
session_start();
include "../db.php"; // Adjust path if needed

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($stmt = $conn->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $db_username, $db_password);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                // Password is correct, start session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $db_username;
                $_SESSION['admin_id'] = $id;

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $error = "Incorrect username or password.";
            }
        } else {
            $error = "Incorrect username or password.";
        }

        $stmt->close();
    } else {
        $error = "Database query failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Login - University Attendance</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600&display=swap');

  * {
    box-sizing: border-box;
  }

  body, html {
    height: 100%;
    margin: 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .login-container {
    background: rgba(0,0,0,0.65);
    padding: 40px 50px;
    border-radius: 15px;
    color: #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,0.7);
    width: 350px;
  }

  h2 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 600;
  }

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
  }

  input[type="text"], input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
  }

  input[type="submit"] {
    width: 100%;
    background-color: #3a8dff;
    color: white;
    padding: 14px;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  input[type="submit"]:hover {
    background-color: #0050cc;
  }

  .error {
    background: #ff4d4d;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
    font-weight: 600;
  }
</style>
</head>
<body>

<div class="login-container">
  <h2>Admin Login</h2>

  <?php if($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required autocomplete="off" />

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required />

    <input type="submit" value="Login" />
  </form>
</div>

</body>
</html>
