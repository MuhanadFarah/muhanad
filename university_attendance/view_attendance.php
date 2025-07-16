<?php
session_start();
require 'db.php';

if (!isset($_SESSION['manage_logged_in'])) {
    header("Location: manage_login.php");
    exit;
}

$sql = "SELECT * FROM attendance ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View University Attendance</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      padding: 0;
    }

    .container {
      background: rgba(255, 255, 255, 0.95);
      margin: 30px auto;
      padding: 25px;
      max-width: 95%;
      border-radius: 10px;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .top-bar .btn {
      padding: 10px 20px;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      color: white;
    }

    .btn-back {
      background-color: #3498db;
    }

    .btn-export {
      background-color: #28a745;
    }

    .btn-back:hover {
      background-color: #2980b9;
    }

    .btn-export:hover {
      background-color: #218838;
    }

    h2 {
      text-align: center;
      color: #333;
    }

    .filters {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
      flex-wrap: wrap;
    }

    .filters input {
      padding: 8px;
      font-size: 14px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    .dataTables_filter {
      display: none !important;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="top-bar">
    <a href="manage_dashboard.php" class="btn btn-back">← Back</a>
    <button id="exportBtn" class="btn btn-export">Export to Excel</button>
  </div>

  <h2>University Attendance Records</h2>

  <div class="filters">
    <input type="text" id="filterTeacher" placeholder="Filter by Teacher ID">
    <input type="text" id="filterStudentName" placeholder="Filter by Student Name">
    <input type="text" id="filterStudentId" placeholder="Filter by Student ID">
    <input type="text" id="filterFaculty" placeholder="Filter by Faculty">
    <input type="text" id="filterSemester" placeholder="Filter by Semester">
    <input type="date" id="filterDate" placeholder="Filter by Date">
  </div>

  <table id="attendanceTable">
    <thead>
      <tr>
        <th>ID</th>
        <th>Teacher ID</th>
        <th>Student ID</th>
        <th>Student Name</th>
        <th>Faculty</th>
        <th>Semester</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['teacher_id']) ?></td>
          <td><?= htmlspecialchars($row['student_id']) ?></td>
          <td><?= htmlspecialchars($row['student_name']) ?></td>
          <td><?= htmlspecialchars($row['faculty']) ?></td>
          <td><?= htmlspecialchars($row['semester']) ?></td>
          <td><?= htmlspecialchars($row['timestamp']) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  $(document).ready(function() {
    const table = $('#attendanceTable').DataTable({
      paging: false,
      info: false,
      dom: 'Bfrtip',
      buttons: [],
      ordering: true
    });

    // Filter fields
    $('#filterTeacher').on('keyup change', function () {
      table.column(1).search(this.value).draw();
    });

    $('#filterStudentId').on('keyup change', function () {
      table.column(2).search(this.value).draw();
    });

    $('#filterStudentName').on('keyup change', function () {
      table.column(3).search(this.value).draw();
    });

    $('#filterFaculty').on('keyup change', function () {
      table.column(4).search(this.value).draw();
    });

    $('#filterSemester').on('keyup change', function () {
      table.column(5).search(this.value).draw();
    });

    $('#filterDate').on('change', function () {
      const selectedDate = this.value;
      table.column(6).search(selectedDate).draw();
    });

    // Export to Excel
    $('#exportBtn').click(function () {
      table.button().add(0, {
        extend: 'excelHtml5',
        title: 'University_Attendance_Export',
        exportOptions: {
          columns: [0,1,2,3,4,5,6]
        }
      });
      table.button(0).trigger();
      table.button().remove();
    });
  });
</script>

</body>
</html>

