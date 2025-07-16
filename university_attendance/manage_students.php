<?php
session_start();
require 'dB.php';

if (!isset($_SESSION['manage_logged_in'])) {
    header("Location: manage_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $faculty = trim($_POST['faculty']);
    $semester = trim($_POST['semester']);

    if ($student_id && $name && $faculty && $semester) {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, faculty, semester) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $student_id, $name, $faculty, $semester);
        $stmt->execute();
        $stmt->close();
        $success = "Student added successfully!";
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('https://grabjobs.co/resources/wp-content/uploads/elementor/thumbs/Highest-Paying-Jobs-in-Australia-q1h61spzd222too1pwfhyojs20424ek2mh6cvmy3yg.webp');
            background-size: cover;
            background-position: center;
            margin: 0;
            padding: 0;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.6);
        }

        .top-bar a {
            text-decoration: none;
            color: white;
            background: #2c3e50;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .top-bar a:hover {
            background: #1a252f;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background: #3498db;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        .message {
            text-align: center;
            color: green;
            margin-bottom: 15px;
        }

        .error {
            text-align: center;
            color: red;
            margin-bottom: 15px;
        }

        .view-btn {
            margin-top: 15px;
            text-align: center;
        }

        .view-btn a {
            background: #27ae60;
            padding: 10px 25px;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .view-btn a:hover {
            background: #1e8449;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="manage_dashboard.php">← Back</a>
        <div class="view-btn">
            <a href="view_students.php">View Students</a>
        </div>
    </div>

    <div class="container">
        <h2>Add Student</h2>

        <?php if (isset($success)): ?>
            <div class="message"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="student_id" placeholder="Student ID" required>
            <input type="text" name="name" placeholder="Student Name" required>
            <input type="text" name="faculty" placeholder="Faculty" required>
            <input type="text" name="semester" placeholder="Semester (e.g. Semester 1)" required>
            <button type="submit">Add Student</button>
        </form>
    </div>

</body>
</html>


