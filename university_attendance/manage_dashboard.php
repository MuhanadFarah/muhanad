<?php
session_start();
if (!isset($_SESSION['manage_logged_in'])) {
    header("Location: manage_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Dashboard</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Background Image */
        body {
            background: url('https://studydestination.com.au/cdn/shop/articles/Top_Attractions_to_visit_in_Australia_1024x1024.jpg?v=1731590271') no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            background-color: rgba(0, 0, 0, 0.6);
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            padding: 15px;
            z-index: 2;
            position: relative;
        }

        .top-bar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            background: #e74c3c;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .top-bar a:hover {
            background: #c0392b;
        }

        .dashboard-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100%;
            position: relative;
            z-index: 2;
        }

        h1 {
            color: white;
            margin-bottom: 40px;
            font-size: 36px;
        }

        .btn {
            padding: 15px 40px;
            font-size: 20px;
            margin: 15px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            background-color: #3498db;
            color: white;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <div class="top-bar">
        <a href="manage_logout.php">Logout</a>
    </div>

    <div class="dashboard-container">
        <h1>Welcome to the Management Dashboard</h1>
        <a href="manage_students.php"><button class="btn">Add Students</button></a>
        <a href="manage_teachers.php"><button class="btn">Add Teachers</button></a>
        <a href="view_attendance.php"><button class="btn">View University Attendance</button></a>
    </div>

</body>
</html>


