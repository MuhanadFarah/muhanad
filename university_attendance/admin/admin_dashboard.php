<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

include "../db.php"; // Adjust path based on your folder structure

// Handle delete blog request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_dashboard.php');
    exit;
}

// Fetch blogs from DB
$blogs = [];
$result = $conn->query("SELECT id, title, content, image_url FROM blogs ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard - Blog Management</title>
<style>
  /* Background and layout */
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url('https://hips.hearstapps.com/hmg-prod/images/nature-quotes-landscape-1648265648.jpg?crop=1xw:0.84375xh;center,top&resize=1200:*') no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    color: #fff;
  }
  /* Overlay for better readability */
  body::before {
    content: "";
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 0;
  }
  /* Container */
  .container {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 3rem auto;
    background: rgba(255, 255, 255, 0.1);
    padding: 2rem 3rem;
    border-radius: 15px;
    box-shadow: 0 0 25px rgba(0,0,0,0.7);
  }
  h1 {
    text-align: center;
    margin-bottom: 2rem;
    font-weight: 700;
  }
  /* Buttons */
  .btn {
    background-color: #3a8dff;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 8px 20px rgba(58, 141, 255, 0.6);
    transition: background-color 0.3s ease;
    display: inline-block;
  }
  .btn:hover {
    background-color: #0050cc;
  }
  .logout-btn {
    float: right;
    background-color: #ff4d4d;
    margin-bottom: 1rem;
  }
  /* Blog Table */
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    color: #fff;
  }
  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.2);
  }
  th {
    background: rgba(58, 141, 255, 0.8);
    font-weight: 700;
  }
  tr:hover {
    background-color: rgba(58, 141, 255, 0.15);
  }
  img.blog-image {
    width: 100px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(0,0,0,0.6);
  }
  .action-links a {
    color: #3a8dff;
    margin-right: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }
  .action-links a:hover {
    color: #0050cc;
  }
  /* Responsive */
  @media(max-width: 768px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }
    th {
      display: none;
    }
    tr {
      margin-bottom: 1.5rem;
      border-bottom: 2px solid rgba(255,255,255,0.2);
    }
    td {
      padding-left: 50%;
      position: relative;
      white-space: normal;
      text-align: right;
    }
    td::before {
      content: attr(data-label);
      position: absolute;
      left: 15px;
      width: 45%;
      padding-left: 15px;
      font-weight: 700;
      text-align: left;
      color: #3a8dff;
    }
    img.blog-image {
      width: 70px;
      height: 45px;
    }
  }
</style>
</head>
<body>
<div class="container">
  <a href="logout.php" class="btn logout-btn">Logout</a>
  <h1>Admin Dashboard - Manage Blogs</h1>
  <a href="add_blog.php" class="btn">+ Add New Blog</a>

  <?php if(count($blogs) > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Image</th>
        <th>Title</th>
        <th>Content Preview</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($blogs as $blog): ?>
      <tr>
        <td data-label="Image">
          <?php if ($blog['image_url']): ?>
            <img src="<?= htmlspecialchars($blog['image_url']) ?>" alt="Blog Image" class="blog-image" />
          <?php else: ?>
            <span>No Image</span>
          <?php endif; ?>
        </td>
        <td data-label="Title"><?= htmlspecialchars($blog['title']) ?></td>
        <td data-label="Content Preview"><?= htmlspecialchars(substr($blog['content'], 0, 100)) ?>...</td>
        <td data-label="Actions" class="action-links">
          <a href="edit_blog.php?id=<?= $blog['id'] ?>">Edit</a>
          <a href="admin_dashboard.php?delete=<?= $blog['id'] ?>" onclick="return confirm('Are you sure you want to delete this blog?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <p>No blogs found. Start by adding a new blog.</p>
  <?php endif; ?>
</div>
</body>
</html>
