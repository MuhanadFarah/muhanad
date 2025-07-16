<?php 
session_start();
require 'db.php';

// Handle AJAX requests for update and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['student_id'])) {
    $action = $_POST['action'];
    $student_id = $_POST['student_id'];

    if ($action === 'update' && isset($_POST['data'])) {
        $data = $_POST['data'];
        if (!isset($data['student_id'], $data['name'], $data['faculty'], $data['semester']) ||
            trim($data['student_id']) === '' || trim($data['name']) === '' || trim($data['faculty']) === '' || trim($data['semester']) === '') {
            echo 'Invalid input';
            exit;
        }

        $new_id = $conn->real_escape_string($data['student_id']);
        $name = $conn->real_escape_string($data['name']);
        $faculty = $conn->real_escape_string($data['faculty']);
        $semester = $conn->real_escape_string($data['semester']);
        $student_id = $conn->real_escape_string($student_id);

        $sql = "UPDATE students SET student_id='$new_id', name='$name', faculty='$faculty', semester='$semester' WHERE student_id='$student_id'";
        echo $conn->query($sql) ? 'success' : $conn->error;
        exit;
    }

    if ($action === 'delete') {
        $student_id = $conn->real_escape_string($student_id);
        $sql = "DELETE FROM students WHERE student_id='$student_id'";
        echo $conn->query($sql) ? 'success' : $conn->error;
        exit;
    }
}

$sql = "SELECT * FROM students ORDER BY student_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Students</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <style>
    body, html {
      margin: 0; padding: 0; height: 100%;
      font-family: Arial, sans-serif;
      background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
      background-size: cover;
      color: #fff;
    }
    .container {
      padding: 20px;
      background: rgba(0,0,0,0.7);
      margin: 20px auto;
      max-width: 95%;
      border-radius: 10px;
      position: relative;
    }
    .btn-back {
      background-color: #4CAF50;
      border: none;
      padding: 10px 18px;
      border-radius: 5px;
      cursor: pointer;
      color: white;
      font-weight: bold;
      text-decoration: none;
    }
    .btn-export {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: #28a745;
      border: none;
      color: white;
      padding: 10px 15px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
    }
    table {
      background: #2c3e50;
      color: white;
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #444;
      text-align: left;
    }
    td.editable {
      cursor: pointer;
    }
    .action-btn {
      cursor: pointer;
      color: #00ff00;
      margin-right: 10px;
      font-weight: bold;
    }
    .delete-btn {
      color: #ff4444;
    }
    input.inline-edit {
      width: 90%;
      padding: 2px;
    }
    .filter-row input {
      width: 100%;
      padding: 5px;
      box-sizing: border-box;
      font-size: 14px;
    }
  </style>
</head>
<body>
<div class="container">
  <a href="manage_students.php" class="btn-back">&larr; Back</a>
  <button class="btn-export" onclick="exportTableToCSV('students_export.csv')">Download</button>
  <h2>Students List</h2>

  <table id="studentsTable">
    <thead>
      <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Faculty</th>
        <th>Semester</th>
        <th>Actions</th>
      </tr>
      <tr class="filter-row">
        <th><input type="text" placeholder="Filter ID" /></th>
        <th><input type="text" placeholder="Filter Name" /></th>
        <th><input type="text" placeholder="Filter Faculty" /></th>
        <th><input type="text" placeholder="Filter Semester" /></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr data-id="<?= htmlspecialchars($row['student_id']) ?>">
        <td class="editable" data-column="student_id"><?= htmlspecialchars($row['student_id']) ?></td>
        <td class="editable" data-column="name"><?= htmlspecialchars($row['name']) ?></td>
        <td class="editable" data-column="faculty"><?= htmlspecialchars($row['faculty']) ?></td>
        <td class="editable" data-column="semester"><?= htmlspecialchars($row['semester']) ?></td>
        <td>
          <span class="action-btn edit-btn">Edit</span>
          <span class="action-btn delete-btn">Delete</span>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
$(function() {
  const table = $('#studentsTable').DataTable({
    paging: false,
    info: false,
    searching: false
  });

  // Top Filter functionality
  $('#studentsTable thead input').on('keyup change clear', function () {
    const index = $(this).parent().index();
    table.column(index).search(this.value).draw();
  });

  // Edit button logic
  $('.edit-btn').click(function() {
    const row = $(this).closest('tr');
    const editing = row.hasClass('editing');
    const cells = row.find('td.editable');

    if (editing) {
      const data = {};
      cells.each(function() {
        const input = $(this).find('input');
        const key = $(this).data('column');
        data[key] = input.val();
      });
      $.post('', {
        action: 'update',
        student_id: row.data('id'),
        data: data
      }, function(response) {
        if (response.trim() === 'success') {
          cells.each(function() {
            const key = $(this).data('column');
            $(this).text(data[key]);
          });
          row.removeClass('editing');
          row.find('.edit-btn').text('Edit');
        } else {
          alert('Update failed: ' + response);
        }
      });
    } else {
      cells.each(function() {
        const text = $(this).text();
        $(this).html('<input class="inline-edit" value="' + text + '" />');
      });
      row.addClass('editing');
      $(this).text('Save');
    }
  });

  // Delete button logic
  $('.delete-btn').click(function() {
    if (!confirm('Are you sure?')) return;
    const row = $(this).closest('tr');
    $.post('', {
      action: 'delete',
      student_id: row.data('id')
    }, function(response) {
      if (response.trim() === 'success') {
        row.remove();
      } else {
        alert('Delete failed: ' + response);
      }
    });
  });
});

// Export to CSV
function exportTableToCSV(filename) {
  const csv = [];
  const rows = document.querySelectorAll("table tr");

  for (let i = 0; i < rows.length; i++) {
    const row = [], cols = rows[i].querySelectorAll("td, th");
    for (let j = 0; j < cols.length - 1; j++) {
      row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
    }
    csv.push(row.join(","));
  }

  const csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
  const downloadLink = document.createElement("a");
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
}
</script>
</body>
</html>

