<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Class Attendance Dashboard</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: url('https://www.tutorialspoint.com/basics_of_computer_science/images/programmer.jpg') no-repeat center center fixed;
      background-size: cover;
    }

    .overlay {
      position: fixed;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.65);
      z-index: 0;
    }

    .dashboard {
      position: relative;
      z-index: 1;
      max-width: 900px;
      margin: 4rem auto;
      padding: 2.5rem 3rem;
      background-color: rgba(255, 255, 255, 0.9);
      border-radius: 18px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      text-align: center;
    }

    h2 {
      font-weight: 800;
      color: #1a2e58;
      margin-bottom: 2rem;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .row > div > a {
      text-decoration: none;
      color: inherit;
    }

    .card {
      border-radius: 14px;
      background: linear-gradient(135deg, #e6f0ff, #c9d9ff);
      color: #1a2e58;
      box-shadow: 0 5px 15px rgba(26,46,88,0.15);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      cursor: pointer;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 1.8rem;
    }

    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 15px 40px rgba(26,46,88,0.35);
      background: linear-gradient(135deg, #b2c7ff, #8faeff);
      color: #0f2047;
    }

    .card i {
      font-size: 3rem;
      margin-bottom: 1rem;
      color: #2a4bd7;
      transition: color 0.3s ease;
    }

    .card:hover i {
      color: #142b7a;
    }

    .card-title {
      font-weight: 700;
      font-size: 1.4rem;
      margin-bottom: 0.5rem;
    }

    .card-text {
      font-size: 1rem;
      line-height: 1.3;
    }

    .logout-btn {
      margin-top: 2rem;
    }

    .btn-logout {
      background: #d93636;
      color: #fff;
      font-weight: 700;
      border-radius: 14px;
      padding: 0.65rem 2.2rem;
      box-shadow: 0 5px 15px rgba(217,54,54,0.4);
      transition: background 0.3s ease, box-shadow 0.3s ease;
      font-size: 1.1rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
    }

    .btn-logout:hover {
      background: #a42424;
      box-shadow: 0 8px 25px rgba(164,36,36,0.6);
      color: #fff;
    }

    @media (max-width: 768px) {
      .dashboard {
        margin: 2rem 1rem;
        padding: 2rem;
      }
    }
  </style>
</head>

<body>
  <div class="overlay"></div>

  <div class="dashboard">
    <h2>Teacher Dashboard</h2>

    <div class="row g-4">
      <div class="col-md-4">
        <a href="scan.php">
          <div class="card">
            <i class="fas fa-qrcode"></i>
            <div class="card-title">Scan Attendance</div>
            <div class="card-text">Quickly scan student ID QR codes.</div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="attendance_records.php">
          <div class="card">
            <i class="fas fa-table-list"></i>
            <div class="card-title">View Records</div>
            <div class="card-text">Review past student attendance logs.</div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="../change_password.php">
          <div class="card">
            <i class="fas fa-key"></i>
            <div class="card-title">Change Password</div>
            <div class="card-text">Securely update your login password.</div>
          </div>
        </a>
      </div>
    </div>

    <div class="logout-btn">
      <a href="logout.php" class="btn btn-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</body>
</html>


