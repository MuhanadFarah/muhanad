<?php
session_start();
require 'db.php';

// Handle AJAX POST requests: insert, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'insert') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if ($name === '' || $phone === '') {
            echo json_encode(['status' => 'error', 'message' => 'Name and phone are required']);
            exit;
        }
        $name = $conn->real_escape_string($name);
        $phone = $conn->real_escape_string($phone);
        $hashed_password = password_hash('admin', PASSWORD_DEFAULT);
        $sql = "INSERT INTO teachers (name, phone, password, must_change_password) VALUES ('$name', '$phone', '$hashed_password', 1)";
        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit;
    }

    if (in_array($action, ['update', 'delete'])) {
        $teacher_id = $conn->real_escape_string($_POST['teacher_id'] ?? '');
        if ($action === 'update') {
            $data = $_POST['data'] ?? [];
            if (!isset($data['name'], $data['phone']) || trim($data['name']) === '' || trim($data['phone']) === '') {
                echo 'Invalid input';
                exit;
            }
            $name = $conn->real_escape_string($data['name']);
            $phone = $conn->real_escape_string($data['phone']);
            $sql = "UPDATE teachers SET name='$name', phone='$phone' WHERE id='$teacher_id'";
            echo $conn->query($sql) ? 'success' : $conn->error;
            exit;
        }
        if ($action === 'delete') {
            $sql = "DELETE FROM teachers WHERE id='$teacher_id'";
            echo $conn->query($sql) ? 'success' : $conn->error;
            exit;
        }
    }
}

// Handle GET: serve teacher list JSON for DataTables
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetch']) && $_GET['fetch'] === 'teachers') {
    $teachers = [];
    $sql = "SELECT id, name, phone, password FROM teachers ORDER BY id DESC";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $teachers[] = $row;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['data' => $teachers]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Teachers - Professional Dashboard</title>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

  * {
    box-sizing: border-box;
  }
  body, html {
    margin: 0; padding: 0; height: 100%;
    font-family: 'Inter', sans-serif;
    background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
    background-size: cover;
    color: #333;
  }

  /* Overlay to soften background */
  body::before {
    content: "";
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    background: rgba(255, 255, 255, 0.85);
    z-index: -1;
  }

  .container {
    max-width: 1200px;
    margin: 40px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 12px 25px rgb(0 0 0 / 0.1);
    padding: 30px 40px;
  }

  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
  }

  .btn {
    background-color: #4CAF50;
    border: none;
    padding: 12px 22px;
    font-weight: 600;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.25s ease;
    text-decoration: none;
    user-select: none;
  }
  .btn:hover {
    background-color: #3a8d40;
  }
  .btn-secondary {
    background-color: #2196F3;
  }
  .btn-secondary:hover {
    background-color: #0d6efd;
  }
  .btn-export {
    background-color: #28a745;
  }
  .btn-export:hover {
    background-color: #1e7e34;
  }

  h2 {
    font-weight: 700;
    font-size: 28px;
    margin: 0;
    color: #222;
  }

  /* Layout for main content: form left, table right */
  .main-content {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
  }

  /* Add teacher form styling */
  .form-container {
    flex: 1 1 320px;
    background: #f9f9f9;
    padding: 30px 25px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.05);
  }
  .form-container h3 {
    margin-bottom: 20px;
    font-weight: 600;
    color: #444;
  }
  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
  }
  input[type="text"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border-radius: 6px;
    border: 1.5px solid #ccc;
    font-size: 16px;
    transition: border-color 0.25s ease;
  }
  input[type="text"]:focus {
    outline: none;
    border-color: #4caf50;
  }
  .form-container button {
    width: 100%;
    padding: 14px;
    font-size: 18px;
    font-weight: 600;
  }

  /* Table container */
  .table-container {
    flex: 2 1 700px;
    background: #fff;
    border-radius: 10px;
    padding: 20px 25px;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
  }

  /* Top controls above table */
  .table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 12px;
  }

  /* Filters group */
  .filters {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
  }
  .filters input {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1.5px solid #ccc;
    font-size: 14px;
    width: 140px;
    transition: border-color 0.25s ease;
  }
  .filters input:focus {
    outline: none;
    border-color: #4caf50;
  }

  /* DataTables styling overrides */
  table.dataTable thead th {
    color: #222 !important;
    font-weight: 600;
    background-color: #f3f3f3;
    border-bottom: 2px solid #ddd !important;
  }
  table.dataTable tbody td {
    color: #444;
    vertical-align: middle;
  }
  .action-btn {
    cursor: pointer;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 5px;
    user-select: none;
    margin-right: 6px;
    transition: background-color 0.2s ease;
  }
  .edit-btn {
    background-color: #2196f3;
    color: white;
  }
  .edit-btn:hover {
    background-color: #0b7dda;
  }
  .delete-btn {
    background-color: #e63946;
    color: white;
  }
  .delete-btn:hover {
    background-color: #b62a32;
  }
  td.editable {
    cursor: pointer;
  }
  input.inline-edit {
    width: 95%;
    padding: 5px;
    font-size: 14px;
  }

  /* Hide default DataTables search */
  .dataTables_filter {
    display: none;
  }

  /* Responsive */
  @media (max-width: 900px) {
    .main-content {
      flex-direction: column;
    }
    .table-container {
      flex: 1 1 100%;
    }
    .form-container {
      flex: 1 1 100%;
    }
  }
</style>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

</head>
<body>
  <div class="container">
    <div class="header">
      <a href="manage_dashboard.php" class="btn btn-back">&larr; Back</a>
      <h2>Manage Teachers</h2>
    </div>

    <div class="main-content">
      <!-- Add Teacher Form -->
      <div class="form-container">
        <h3>Add New Teacher</h3>
        <form id="addTeacherForm" autocomplete="off">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" required placeholder="Enter teacher's full name" />

          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" required placeholder="Enter phone number" />

          <button type="submit" class="btn">Add Teacher</button>
        </form>
      </div>

      <!-- Teachers Table -->
      <div class="table-container">
        <div class="table-controls">
          <div class="filters">
            <input type="text" id="filterId" placeholder="Filter by ID" />
            <input type="text" id="filterName" placeholder="Filter by Name" />
            <input type="text" id="filterPhone" placeholder="Filter by Phone" />
          </div>
          <button id="exportBtn" class="btn btn-export">Export to Excel</button>
        </div>
        <table id="teachersTable" class="display" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Password (Hashed)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

<script>
$(document).ready(function() {
  // Add Teacher form submission
  $('#addTeacherForm').on('submit', function(e) {
    e.preventDefault();
    $.post('', {
      action: 'insert',
      name: $('#name').val(),
      phone: $('#phone').val()
    }, function(resp) {
      let res = JSON.parse(resp);
      if (res.status === 'success') {
        alert('Teacher added successfully.');
        $('#name').val('');
        $('#phone').val('');
        table.ajax.reload();
      } else {
        alert('Error: ' + res.message);
      }
    });
  });

let table = $('#teachersTable').DataTable({
  ajax: 'manage_teachers.php?fetch=teachers',
  columns: [
    { data: 'id' },
    { data: 'name' },
    { data: 'phone' },
    { data: 'password' },
    {
      data: null,
      orderable: false,
      render: function(data, type, row) {
        return '<span class="action-btn edit-btn">Edit</span>' +
               '<span class="action-btn delete-btn">Delete</span>';
      }
    }
  ],
  order: [[0, 'desc']],
  dom: 'Bfrtip',
  buttons: [],
  paging: false,           // 👈 disables pagination
  scrollY: '60vh',         // 👈 vertical scroll height
  scrollCollapse: true     // 👈 shrink scroll area if few rows
});


  // Filters
  $('#filterId').on('keyup change clear', function() {
    if (table.column(0).search() !== this.value) {
      table.column(0).search(this.value).draw();
    }
  });
  $('#filterName').on('keyup change clear', function() {
    if (table.column(1).search() !== this.value) {
      table.column(1).search(this.value).draw();
    }
  });
  $('#filterPhone').on('keyup change clear', function() {
    if (table.column(2).search() !== this.value) {
      table.column(2).search(this.value).draw();
    }
  });

  // Export button
  $('#exportBtn').click(function() {
    table.button().add(0, {
      extend: 'excelHtml5',
      title: 'Teachers List',
      exportOptions: { columns: [0,1,2,3] }
    });
    table.button(0).trigger();
    table.button().remove();
  });

  // Inline edit/save for name and phone
  $('#teachersTable tbody').on('click', '.edit-btn', function() {
    const row = $(this).closest('tr');
    const isEditing = row.hasClass('editing');
    const cells = row.find('td').not(':last'); // all but last column (actions)

    if (isEditing) {
      const data = {
        name: cells.eq(1).find('input').val(),
        phone: cells.eq(2).find('input').val()
      };
      const teacherId = row.find('td').eq(0).text();
      if (!data.name || !data.phone) {
        alert('Name and phone cannot be empty');
        return;
      }
      $.post('', {
        action: 'update',
        teacher_id: teacherId,
        data: data
      }, function(response) {
        if (response.trim() === 'success') {
          cells.eq(1).text(data.name);
          cells.eq(2).text(data.phone);
          row.removeClass('editing');
          row.find('.edit-btn').text('Edit');
        } else {
          alert('Update failed: ' + response);
        }
      });
    } else {
      cells.eq(1).html('<input type="text" value="'+cells.eq(1).text()+'" />');
      cells.eq(2).html('<input type="text" value="'+cells.eq(2).text()+'" />');
      row.addClass('editing');
      $(this).text('Save');
    }
  });

  // Delete teacher
  $('#teachersTable tbody').on('click', '.delete-btn', function() {
    if (!confirm('Are you sure you want to delete this teacher?')) return;
    const row = $(this).closest('tr');
    const teacherId = row.find('td').eq(0).text();
    $.post('', {
      action: 'delete',
      teacher_id: teacherId
    }, function(response) {
      if (response.trim() === 'success') {
        table.row(row).remove().draw();
      } else {
        alert('Delete failed: ' + response);
      }
    });
  });
});
</script>

</body>
</html>



