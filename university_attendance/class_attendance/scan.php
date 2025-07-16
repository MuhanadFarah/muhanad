<?php  
session_start();
include "../db.php";

if (!isset($_SESSION['teacher_logged_in'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['scannedData'])) {
    $data = explode(",", trim($_POST['scannedData']));
    
    if (count($data) === 4) {
        $student_id = trim($data[0]);
        $student_name = trim($data[1]);
        $faculty = trim($data[2]);
        $semester = trim($data[3]);
        $date = date('Y-m-d');
        $teacher_id = $_SESSION['teacher_id'];

        $checkStudent = $conn->prepare("SELECT * FROM students WHERE student_id = ? AND name = ? AND faculty = ? AND semester = ?");
        $checkStudent->bind_param("ssss", $student_id, $student_name, $faculty, $semester);
        $checkStudent->execute();
        $studentResult = $checkStudent->get_result();

        if ($studentResult->num_rows > 0) {
            $stmt = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
            $stmt->bind_param("ss", $student_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $insert = $conn->prepare("INSERT INTO attendance (student_id, student_name, faculty, semester, date, teacher_id) VALUES (?, ?, ?, ?, ?, ?)");
                $insert->bind_param("sssssi", $student_id, $student_name, $faculty, $semester, $date, $teacher_id);
                $insert->execute();
                $message = "✅ Attendance marked for $student_name";
            } else {
                $message = "⚠ Attendance already marked for $student_name";
            }
        } else {
            $message = "❌ Student not found.";
        }
    } else {
        $message = "❌ Invalid QR format. Expected: student_id, name, faculty, semester";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  <title>QR Scan | University Attendance</title>

  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    /* Reset and basics */
    * {
      box-sizing: border-box;
    }
    body, html {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: url('https://www.uniqode.com/blog/wp-content/uploads/2023/07/How-to-create-a-free-QR-Code-for-attendance.png') no-repeat center center fixed;
      background-size: cover;
      overflow: hidden; /* Prevent scrolling */
    }

    .overlay {
      background: rgba(0, 0, 0, 0.6);
      position: fixed;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 0;
    }

    .back-btn {
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 5;
    }

    .back-btn a {
      font-weight: 600;
      color: #f0f0f0;
      background-color: rgba(42, 75, 215, 0.85);
      padding: 0.5rem 1rem;
      border-radius: 12px;
      text-decoration: none;
      user-select: none;
      box-shadow: 0 3px 8px rgba(26, 46, 88, 0.7);
      transition: background-color 0.3s ease;
      font-size: 1rem;
    }
    .back-btn a:hover {
      background-color: #1a2e58;
      color: #fff;
    }

    .container {
      position: relative;
      z-index: 2;
      max-width: 700px;
      width: 100%;
      margin: auto;
      height: 100vh; /* Full viewport height */
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.35);
      padding: 1rem 2rem 2rem;
      overflow: hidden;
    }

    h2 {
      color: #1a2e58;
      font-weight: 900;
      margin-bottom: 1rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      text-shadow: 1px 1px 3px rgba(26, 46, 88, 0.2);
      user-select: none;
    }

    /* Scanner viewport container */
    #scanner-frame {
      width: 100%;
      max-width: 500px;
      flex-grow: 1;
      border-radius: 15px;
      border: 6px solid #2a4bd7;
      box-shadow: 0 8px 30px rgba(42, 75, 215, 0.6);
      background: #f0f5ff;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    /* Perfect square for #reader */
    #reader {
      width: 100% !important;
      height: 0 !important;
      padding-bottom: 100% !important; /* Square ratio */
      border-radius: 9px;
      outline: none;
      box-sizing: border-box;
      position: relative;
    }

    /* Ensure video/canvas fill the square */
    #reader > canvas, 
    #reader > video {
      position: absolute !important;
      top: 0;
      left: 0;
      width: 100% !important;
      height: 100% !important;
      border-radius: 9px;
    }

    .message {
      font-size: 1.25rem;
      margin-top: 1rem;
      font-weight: 700;
      color: #154360;
      user-select: none;
      text-shadow: 1px 1px 2px rgba(21, 67, 96, 0.3);
      min-height: 2.5rem; /* reserve space to avoid layout shift */
      text-align: center;
    }

    .controls {
      margin-top: 0.8rem;
      width: 100%;
      max-width: 500px;
      display: flex;
      justify-content: center;
    }

    #stopBtn {
      font-weight: 700;
      background-color: #d93636;
      border: none;
      border-radius: 14px;
      padding: 0.65rem 2.3rem;
      box-shadow: 0 6px 18px rgba(217, 54, 54, 0.45);
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      color: white;
      text-transform: uppercase;
      min-width: 140px;
    }
    #stopBtn:hover:not(:disabled) {
      background-color: #a42424;
      box-shadow: 0 8px 30px rgba(164, 36, 36, 0.7);
    }
    #stopBtn:disabled {
      background-color: #e89090;
      cursor: not-allowed;
      box-shadow: none;
    }

    @media (max-width: 550px) {
      .container {
        max-width: 100vw;
        border-radius: 0;
        padding: 1rem;
        height: 100vh;
      }
      #scanner-frame, .controls {
        max-width: 100%;
      }
    }
  </style>
</head>

<body>
  <div class="overlay"></div>

  <div class="back-btn">
    <a href="http://localhost:8080/university_attendance/class_attendance">
      ← Back to Dashboard
    </a>
  </div>

  <div class="container" role="main">
    <h2>Scan Student QR</h2>

    <?php if ($message): ?>
      <div class="message alert alert-info" role="alert"><?= htmlspecialchars($message) ?></div>
    <?php else: ?>
      <div class="message" aria-live="polite" aria-atomic="true">&nbsp;</div>
    <?php endif; ?>

    <div id="scanner-frame" aria-label="QR code scanner viewport">
      <div id="reader" tabindex="0"></div>
    </div>

    <div class="controls">
      <button id="stopBtn" aria-label="Stop scanning">Stop Scan</button>
    </div>
  </div>

  <form method="POST" id="submitForm" style="display:none;">
    <input type="hidden" name="scannedData" id="scannedData" />
  </form>

  <script>
    let scanner;
    const stopBtn = document.getElementById("stopBtn");
    const messageEl = document.querySelector('.message');

    function startScanning() {
      scanner = new Html5Qrcode("reader");
      stopBtn.disabled = false;

      scanner.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 300, height: 300 } },
        qrMessage => {
          console.log("QR Code detected:", qrMessage);
          document.getElementById("scannedData").value = qrMessage;
          document.getElementById("submitForm").submit();

          // Show scanning message while processing
          messageEl.textContent = "Processing...";
        },
        errorMessage => {
          // no-op for scan errors to keep scanning
        }
      ).catch(err => {
        alert("Error starting the scanner: " + err);
        stopBtn.disabled = true;
      });
    }

    stopBtn.onclick = () => {
      if (scanner) {
        scanner.stop().then(() => {
          document.getElementById("reader").innerHTML = "";
          stopBtn.disabled = true;
          messageEl.textContent = "Scanning stopped.";
        }).catch(err => {
          console.error("Stop error:", err);
        });
      }
    };

    window.addEventListener("load", () => {
      startScanning();
    });
  </script>
</body>
</html>


















