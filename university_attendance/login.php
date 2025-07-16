<?php  
session_start();
include "db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if ($stmt = $conn->prepare("SELECT id, name, password, must_change_password FROM teachers WHERE phone = ?")) {
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $teacher = $result->fetch_assoc();
            $storedPassword = $teacher['password'];

            if (($storedPassword === 'admin' && $password === 'admin') || password_verify($password, $storedPassword)) {
                $_SESSION['teacher_logged_in'] = true;
                $_SESSION['teacher_id'] = $teacher['id'];
                $_SESSION['teacher_name'] = $teacher['name'];

                if ($teacher['must_change_password'] == 1 || $storedPassword === 'admin') {
                    header("Location: change_password.php");
                    exit();
                } else {
                    header("Location: class_attendance");
                    exit();
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Phone number not found.";
        }

        $stmt->close();
    } else {
        $error = "Database query error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Teacher Login | University Attendance</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(23, 42, 69, 0.7);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 3rem 3.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.35);
            color: #173a45;
            text-align: center;
        }

        .login-container h2 {
            font-weight: 700;
            margin-bottom: 2rem;
            color: #1c4966;
            letter-spacing: 0.08em;
            font-size: 2.2rem;
            text-transform: uppercase;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        label {
            font-weight: 600;
            color: #234e70;
            text-align: left;
            display: block;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #82aaff;
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #4c8cff;
            box-shadow: 0 0 12px rgba(76, 140, 255, 0.6);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(90deg, #4c8cff, #2a67ff);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            padding: 0.85rem 0;
            font-size: 1.2rem;
            width: 100%;
            box-shadow: 0 8px 20px rgba(42, 103, 255, 0.5);
            transition: background 0.3s ease;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
            margin-top: 1rem;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #2a67ff, #1a4ecc);
        }

        .error-message {
            background-color: #ffdddd;
            color: #b71c1c;
            border-radius: 12px;
            padding: 14px 18px;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border: 1.5px solid #e53935;
            text-align: center;
            box-shadow: 0 0 8px rgba(229, 115, 115, 0.7);
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 999;
            text-decoration: none;
            color: #2a67ff;
            font-weight: 700;
            border: 2px solid #2a67ff;
            padding: 0.4rem 1rem;
            border-radius: 10px;
            transition: background-color 0.3s ease, color 0.3s ease;
            user-select: none;
        }

        .back-button:hover {
            background-color: #2a67ff;
            color: #fff;
        }

        footer {
            position: fixed;
            bottom: 1rem;
            width: 100%;
            text-align: center;
            color: #d1d9e6;
            font-size: 0.9rem;
            user-select: none;
            z-index: 2;
            font-weight: 500;
            text-shadow: 0 0 6px rgba(0, 0, 0, 0.7);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 2rem;
                border-radius: 12px;
                max-width: 95vw;
            }

            .login-container h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="overlay"></div>

    <a href="http://localhost:8080/university_attendance/" class="back-button" aria-label="Back to University Attendance homepage">
        ← Back
    </a>

    <section class="login-container" aria-label="Teacher login form">
        <h2>Teacher Login</h2>

        <?php if ($error) : ?>
            <div class="error-message" role="alert" aria-live="assertive">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-4">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="252XXXXXXXXX" required autofocus />
            </div>

            <div class="mb-4">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required />
            </div>

            <button type="submit" class="btn btn-primary" aria-label="Log in to your account">
                Log In
            </button>
        </form>
    </section>

    <footer>
        &copy; <?php echo date('Y'); ?> University Attendance System
    </footer>
</body>
</html>

