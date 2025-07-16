<?php
include "../db.php";

$date = $_GET['date'] ?? '';
$student = $_GET['student'] ?? '';
$faculty = $_GET['faculty'] ?? '';

$query = "SELECT * FROM attendance WHERE 1=1";
$params = [];
$types = "";

if (!empty($date)) {
    $query .= " AND date = ?";
    $params[] = $date;
    $types .= "s";
}
if (!empty($student)) {
    $query .= " AND student_name LIKE ?";
    $params[] = "%" . $student . "%";
    $types .= "s";
}
if (!empty($faculty)) {
    $query .= " AND faculty LIKE ?";
    $params[] = "%" . $faculty . "%";
    $types .= "s";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

header("Content-Type: text/csv");
header("Content-Disposition: attachment;filename=attendance_export.csv");

$output = fopen("php://output", "w");
fputcsv($output, ["Student ID", "Name", "Faculty", "Date", "Time"]);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_id'],
        $row['student_name'],
        $row['faculty'],
        $row['date'],
        date("H:i:s", strtotime($row['created_at'] ?? $row['date']))
    ]);
}
fclose($output);
exit;
