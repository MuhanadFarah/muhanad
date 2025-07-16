<?php
session_start();
include "db.php";

// Redirect to login if teacher not logged in
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in']) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        $teacher_id = $_SESSION['teacher_id'];
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_stmt = $conn->prepare("UPDATE teachers SET password = ?, must_change_password = 0 WHERE id = ?");
        $update_stmt->bind_param("si", $new_hashed_password, $teacher_id);

        if ($update_stmt->execute()) {
            $success = "Password changed successfully. Redirecting to dashboard...";
            header("refresh:3; url=class_attendance/index.php");
        } else {
            $error = "Failed to update password. Please try again.";
        }

        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Change Password | University Attendance</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(23, 42, 69, 0.7);
            z-index: 1;
        }
        .change-password-container {
            position: relative;
            z-index: 2;
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            padding: 3rem 3.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.35);
            color: #173a45;
        }
        h2 {
            font-weight: 700;
            margin-bottom: 2rem;
            color: #1c4966;
            text-align: center;
            letter-spacing: 0.08em;
            font-size: 2.2rem;
            text-transform: uppercase;
            text-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        label {
            font-weight: 600;
            color: #234e70;
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
            box-shadow: 0 0 12px rgba(76,140,255,0.6);
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
            box-shadow: 0 8px 20px rgba(42,103,255,0.5);
            transition: background 0.3s ease;
            color: #fff;
            text-shadow: 0 1px 3px rgba(0,0,0,0.25);
            cursor: pointer;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2a67ff, #1a4ecc);
        }
        .alert {
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
            box-shadow: 0 0 8px rgba(229,115,115,0.7);
        }
        .alert-danger {
            background-color: #ffdddd;
            color: #b71c1c;
            border: 1.5px solid #e53935;
        }
        .alert-success {
            background-color: #ddffdd;
            color: #2e7d32;
            border: 1.5px solid #43a047;
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
            text-shadow: 0 0 6px rgba(0,0,0,0.7);
        }
        @media (max-width: 480px) {
            .change-password-container {
                padding: 2rem 2rem;
                border-radius: 12px;
                max-width: 95vw;
            }
            h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <section class="change-password-container" aria-label="Change Password Form">
        <h2>Change Password</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert" aria-live="assertive"><?=htmlspecialchars($error)?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success" role="alert" aria-live="polite"><?=htmlspecialchars($success)?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-4">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password" required />
            </div>

            <div class="mb-4">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" required />
            </div>

            <button type="submit" class="btn btn-primary" aria-label="Change password">
                Change Password
            </button>
        </form>
    </section>

    <footer>
        &copy; <?=date('Y')?> University Attendance System
    </footer>
</body>
</html>


