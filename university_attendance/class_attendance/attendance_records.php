<?php
session_start();
include "../db.php";

if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Filtering logic
$filter_date = $_GET['date'] ?? '';
$filter_student = $_GET['student'] ?? '';
$filter_faculty = $_GET['faculty'] ?? '';

$query = "SELECT * FROM attendance WHERE teacher_id = ?";
$params = [$teacher_id];
$types = "i";

if (!empty($filter_date)) {
    $query .= " AND date = ?";
    $params[] = $filter_date;
    $types .= "s";
}
if (!empty($filter_student)) {
    $query .= " AND student_name LIKE ?";
    $params[] = "%" . $filter_student . "%";
    $types .= "s";
}
if (!empty($filter_faculty)) {
    $query .= " AND faculty LIKE ?";
    $params[] = "%" . $filter_faculty . "%";
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => 'Invalid request'];

    if ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        $delStmt = $conn->prepare("DELETE FROM attendance WHERE id = ?");
        $delStmt->bind_param("i", $id);
        if ($delStmt->execute()) {
            $response = ['success' => true];
        } else {
            $response['message'] = "Failed to delete record.";
        }
        echo json_encode($response);
        exit;
    }

    if ($_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $student_id = trim($_POST['student_id']);
        $student_name = trim($_POST['student_name']);
        $faculty = trim($_POST['faculty']);
        $semester = trim($_POST['semester']);  // Added semester here

        // Validate inputs (basic)
        if ($student_id === '' || $student_name === '' || $faculty === '' || $semester === '') {
            $response['message'] = "All fields are required.";
            echo json_encode($response);
            exit;
        }

        $updStmt = $conn->prepare("UPDATE attendance SET student_id = ?, student_name = ?, faculty = ?, semester = ? WHERE id = ?");
        $updStmt->bind_param("ssssi", $student_id, $student_name, $faculty, $semester, $id);
        if ($updStmt->execute()) {
            $response = ['success' => true];
        } else {
            $response['message'] = "Failed to update record.";
        }
        echo json_encode($response);
        exit;
    }

    if ($_POST['action'] === 'add') {
        $student_id = trim($_POST['student_id']);
        $student_name = trim($_POST['student_name']);
        $faculty = trim($_POST['faculty']);
        $semester = trim($_POST['semester']); // Added semester here
        $date = date('Y-m-d');

        if ($student_id === '' || $student_name === '' || $faculty === '' || $semester === '') {
            $response['message'] = "All fields are required.";
            echo json_encode($response);
            exit;
        }

        $insertStmt = $conn->prepare("INSERT INTO attendance (student_id, student_name, faculty, semester, date, teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
        $insertStmt->bind_param("sssssi", $student_id, $student_name, $faculty, $semester, $date, $teacher_id);

        if ($insertStmt->execute()) {
            $newId = $insertStmt->insert_id;
            $response = [
                'success' => true,
                'id' => $newId,
                'student_id' => $student_id,
                'student_name' => $student_name,
                'faculty' => $faculty,
                'semester' => $semester,
                'date' => $date,
                'time_arrived' => date("H:i:s")
            ];
        } else {
            $response['message'] = "Failed to add record.";
        }
        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Attendance Records</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: url('https://hips.hearstapps.com/hmg-prod/images/nature-quotes-landscape-1648265648.jpg?crop=1xw:0.84375xh;center,top&resize=1200:*') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', sans-serif;
      padding-top: 60px;
    }
    .container {
      background: rgba(255, 255, 255, 0.96);
      padding: 2rem;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0,0,0,0.2);
    }
    h2 {
      font-weight: 700;
      text-align: center;
      color: #1a2e58;
      margin-bottom: 2rem;
    }
    .form-control, .btn {
      border-radius: 8px;
    }
    .table thead {
      background-color: #1a2e58;
      color: white;
    }
    .btn-edit, .btn-delete, .btn-save, .btn-cancel {
      font-size: 0.85rem;
      padding: 0.3rem 0.7rem;
      border-radius: 6px;
      margin-right: 0.3rem;
      cursor: pointer;
      user-select: none;
    }
    .btn-edit {
      background-color: #2a4bd7;
      color: white;
      border: none;
    }
    .btn-edit:hover {
      background-color: #1a2e58;
    }
    .btn-delete {
      background-color: #d93636;
      color: white;
      border: none;
    }
    .btn-delete:hover {
      background-color: #a42424;
    }
    .btn-save {
      background-color: #28a745;
      color: white;
      border: none;
    }
    .btn-save:hover {
      background-color: #1e7e34;
    }
    .btn-cancel {
      background-color: #6c757d;
      color: white;
      border: none;
    }
    .btn-cancel:hover {
      background-color: #565e64;
    }
    .add-row input {
      width: 100%;
      border-radius: 6px;
      border: 1px solid #ced4da;
      padding: 0.375rem 0.75rem;
    }
    .add-button {
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Attendance Records</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-3">
        <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>" class="form-control" />
      </div>
      <div class="col-md-3">
        <input type="text" name="student" placeholder="Student name" value="<?= htmlspecialchars($filter_student) ?>" class="form-control" />
      </div>
      <div class="col-md-3">
        <input type="text" name="faculty" placeholder="Faculty" value="<?= htmlspecialchars($filter_faculty) ?>" class="form-control" />
      </div>
      <div class="col-md-3 d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="attendance_records.php" class="btn btn-secondary">Reset</a>
      </div>
    </form>

    <!-- Export + Back -->
    <div class="mb-3 d-flex justify-content-between">
      <a href="export_attendance.php?date=<?=urlencode($filter_date)?>&student=<?=urlencode($filter_student)?>&faculty=<?=urlencode($filter_faculty)?>" class="btn btn-success">Download</a>
      <a href="http://localhost:8080/university_attendance/class_attendance/" class="btn btn-dark">← Back</a>
    </div>

    <!-- Add New Attendance Button -->
    <button id="showAddRowBtn" class="btn btn-primary add-button">+ Add New Attendance</button>

    <!-- Add New Attendance Row (hidden initially) -->
    <table class="table table-bordered table-hover align-middle" id="addTable" style="display:none; margin-bottom: 1rem;">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th>Faculty</th>
          <th>Semester</th> <!-- Added -->
          <th>Date</th>
          <th>Time Arrived</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr class="add-row">
          <td><input type="text" id="add_student_id" placeholder="Student ID" /></td>
          <td><input type="text" id="add_student_name" placeholder="Student Name" /></td>
          <td><input type="text" id="add_faculty" placeholder="Faculty" /></td>
          <td><input type="text" id="add_semester" placeholder="Semester" /></td> <!-- Added -->
          <td><?= date('Y-m-d') ?></td>
          <td><?= date('H:i:s') ?></td>
          <td>
            <button id="addSaveBtn" class="btn btn-save">Save</button>
            <button id="addCancelBtn" class="btn btn-cancel">Cancel</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Records Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle" id="attendanceTable">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Faculty</th>
            <th>Semester</th> <!-- Added -->
            <th>Date</th>
            <th>Time Arrived</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
          <tr data-id="<?= $row['id'] ?>">
            <td class="student_id"><?= htmlspecialchars($row['student_id']) ?></td>
            <td class="student_name"><?= htmlspecialchars($row['student_name']) ?></td>
            <td class="faculty"><?= htmlspecialchars($row['faculty']) ?></td>
            <td class="semester"><?= htmlspecialchars($row['semester']) ?></td> <!-- Added -->
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars(date("H:i:s", strtotime($row['timestamp'] ?? $row['date']))) ?></td>
            <td>
              <button class="btn-edit">Edit</button>
              <button class="btn-delete">Delete</button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const attendanceTable = document.getElementById('attendanceTable');
  const showAddRowBtn = document.getElementById('showAddRowBtn');
  const addTable = document.getElementById('addTable');
  const addSaveBtn = document.getElementById('addSaveBtn');
  const addCancelBtn = document.getElementById('addCancelBtn');

  // Show Add Row
  showAddRowBtn.addEventListener('click', () => {
    addTable.style.display = 'table';
    showAddRowBtn.style.display = 'none';
  });

  // Cancel Add Row
  addCancelBtn.addEventListener('click', () => {
    clearAddRowInputs();
    addTable.style.display = 'none';
    showAddRowBtn.style.display = 'inline-block';
  });

  // Save Add Row
  addSaveBtn.addEventListener('click', () => {
    const student_id = document.getElementById('add_student_id').value.trim();
    const student_name = document.getElementById('add_student_name').value.trim();
    const faculty = document.getElementById('add_faculty').value.trim();
    const semester = document.getElementById('add_semester').value.trim(); // Added

    if (!student_id || !student_name || !faculty || !semester) {
      alert('Please fill in all fields.');
      return;
    }

    // AJAX POST to add
    fetch('', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'add',
        student_id,
        student_name,
        faculty,
        semester // Added
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        // Append new row to table
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-id', data.id);
        newRow.innerHTML = `
          <td class="student_id">${escapeHtml(data.student_id)}</td>
          <td class="student_name">${escapeHtml(data.student_name)}</td>
          <td class="faculty">${escapeHtml(data.faculty)}</td>
          <td class="semester">${escapeHtml(data.semester)}</td> <!-- Added -->
          <td>${escapeHtml(data.date)}</td>
          <td>${escapeHtml(data.time_arrived)}</td>
          <td>
            <button class="btn-edit">Edit</button>
            <button class="btn-delete">Delete</button>
          </td>
        `;
        attendanceTable.querySelector('tbody').appendChild(newRow);

        clearAddRowInputs();
        addTable.style.display = 'none';
        showAddRowBtn.style.display = 'inline-block';
      } else {
        alert(data.message || 'Failed to add record.');
      }
    })
    .catch(() => alert('Network error.'));
  });

  function clearAddRowInputs() {
    document.getElementById('add_student_id').value = '';
    document.getElementById('add_student_name').value = '';
    document.getElementById('add_faculty').value = '';
    document.getElementById('add_semester').value = ''; // Added
  }

  // Escape HTML for safe insertion
  function escapeHtml(text) {
    return text.replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[m]);
  }

  // Delegate edit and delete buttons
  attendanceTable.addEventListener('click', e => {
    const target = e.target;
    const row = target.closest('tr');
    if (!row) return;

    const id = row.getAttribute('data-id');

    if (target.classList.contains('btn-delete')) {
      if (confirm(`Are you sure you want to delete attendance for "${row.querySelector('.student_name').textContent}"?`)) {
        // AJAX delete
        fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ action: 'delete', id })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            row.remove();
          } else {
            alert(data.message || 'Failed to delete record.');
          }
        })
        .catch(() => alert('Network error.'));
      }
    }

    if (target.classList.contains('btn-edit')) {
      if (row.classList.contains('editing')) return; // already editing

      row.classList.add('editing');

      // Backup current values
      const studentIdCell = row.querySelector('.student_id');
      const studentNameCell = row.querySelector('.student_name');
      const facultyCell = row.querySelector('.faculty');
      const semesterCell = row.querySelector('.semester'); // Added
      const actionsCell = target.parentElement;

      const studentIdVal = studentIdCell.textContent;
      const studentNameVal = studentNameCell.textContent;
      const facultyVal = facultyCell.textContent;
      const semesterVal = semesterCell.textContent; // Added

      // Replace cells with input fields
      studentIdCell.innerHTML = `<input type="text" class="form-control" value="${escapeHtml(studentIdVal)}">`;
      studentNameCell.innerHTML = `<input type="text" class="form-control" value="${escapeHtml(studentNameVal)}">`;
      facultyCell.innerHTML = `<input type="text" class="form-control" value="${escapeHtml(facultyVal)}">`;
      semesterCell.innerHTML = `<input type="text" class="form-control" value="${escapeHtml(semesterVal)}">`; // Added

      // Change action buttons to Save / Cancel
      actionsCell.innerHTML = `
        <button class="btn-save btn">Save</button>
        <button class="btn-cancel btn">Cancel</button>
      `;
    }

    if (target.classList.contains('btn-cancel')) {
      // Cancel editing, restore original row display
      const row = target.closest('tr');
      const id = row.getAttribute('data-id');
      row.classList.remove('editing');

      // Reload original data from cells backup (or just refresh page)
      // For simplicity, reload page:
      location.reload();
    }

    if (target.classList.contains('btn-save')) {
      // Save edited data
      const row = target.closest('tr');
      const id = row.getAttribute('data-id');

      const inputs = row.querySelectorAll('input.form-control');
      const student_id = inputs[0].value.trim();
      const student_name = inputs[1].value.trim();
      const faculty = inputs[2].value.trim();
      const semester = inputs[3].value.trim(); // Added

      if (!student_id || !student_name || !faculty || !semester) {
        alert('Please fill all fields.');
        return;
      }

      // AJAX update
      fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'edit',
          id,
          student_id,
          student_name,
          faculty,
          semester // Added
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Update row cells to new values
          row.querySelector('.student_id').textContent = student_id;
          row.querySelector('.student_name').textContent = student_name;
          row.querySelector('.faculty').textContent = faculty;
          row.querySelector('.semester').textContent = semester; // Added

          // Restore action buttons
          row.querySelector('td:last-child').innerHTML = `
            <button class="btn-edit btn">Edit</button>
            <button class="btn-delete btn">Delete</button>
          `;

          row.classList.remove('editing');
        } else {
          alert(data.message || 'Failed to update record.');
        }
      })
      .catch(() => alert('Network error.'));
    }
  });
});
</script>
</body>
</html>



