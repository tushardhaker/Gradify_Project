<?php
include '../db.php';

session_start();

$faculty = $_SESSION["username"];
$date = $_POST['attendance_date'];

extract($_POST);

foreach ($status as $roll => $att_status) {
    $student = $conn->query("SELECT name FROM user WHERE rollno = '$roll'")->fetch_assoc();
    $name = $student['name'];

    $stmt = $conn->prepare("INSERT INTO attendance (faculty_name, session, branch, semester, section, subject, date, rollno, name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $faculty, $session, $branch, $semester, $section, $subject, $date, $roll, $name, $att_status);
    $stmt->execute();
}

echo "<script>alert('Attendance submitted successfully!'); window.location.href='faculty_attendance_filter.php';</script>";
?>