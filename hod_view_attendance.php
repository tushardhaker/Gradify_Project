<?php
session_start();
include '../db.php'; // Database connection

// Check if the user is logged in and has the 'HOD' role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'HOD') {
    header('Location: /dept/login.php');
    exit();
}

// Fetch data for filters
$sessionQuery = "SELECT DISTINCT session FROM user";
$semesterQuery = "SELECT DISTINCT semester FROM user";
$sectionQuery = "SELECT DISTINCT section FROM user";
$subjectQuery = "SELECT DISTINCT subject FROM attendance";
$dateQuery = "SELECT DISTINCT date FROM attendance";

$sessionResult = $conn->query($sessionQuery);
$semesterResult = $conn->query($semesterQuery);
$sectionResult = $conn->query($sectionQuery);
$subjectResult = $conn->query($subjectQuery);
$dateResult = $conn->query($dateQuery);

// Filter values
$session = isset($_POST['session']) ? $_POST['session'] : '';
$semester = isset($_POST['semester']) ? $_POST['semester'] : '';
$section = isset($_POST['section']) ? $_POST['section'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';

// Base attendance query
$attendanceQuery = "SELECT * FROM attendance WHERE 1";

if ($session) {
    $attendanceQuery .= " AND rollno IN (SELECT rollno FROM user WHERE session = '$session')";
}
if ($semester) {
    $attendanceQuery .= " AND rollno IN (SELECT rollno FROM user WHERE semester = '$semester')";
}
if ($section) {
    $attendanceQuery .= " AND rollno IN (SELECT rollno FROM user WHERE section = '$section')";
}
if ($subject) {
    $attendanceQuery .= " AND subject = '$subject'";
}
if ($date) {
    $attendanceQuery .= " AND date = '$date'";
}

$attendanceResult = $conn->query($attendanceQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HOD View Attendance</title>
    <link rel="stylesheet" href="/dept/css/attendance.css">
</head>
<body>
<nav class="navbar">
    <div class="logo">CSE(AI) Department</div>
    <ul class="nav-links">
        <li><a href="/dept/Home.php">Home</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="/dept/logout.php" class="logout-btn">Logout</a></li>
    </ul>
</nav>

<div class="filter-container">
    <form method="post" action="">
        <label for="session">Session:</label>
        <select name="session" id="session">
            <option value="">Select Session</option>
            <?php while ($row = $sessionResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['session']; ?>" <?php if ($session == $row['session']) echo 'selected'; ?>>
                    <?php echo $row['session']; ?>
                </option>
            <?php } ?>
        </select>

        <label for="semester">Semester:</label>
        <select name="semester" id="semester">
            <option value="">Select Semester</option>
            <?php while ($row = $semesterResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['semester']; ?>" <?php if ($semester == $row['semester']) echo 'selected'; ?>>
                    <?php echo $row['semester']; ?>
                </option>
            <?php } ?>
        </select>

        <label for="section">Section:</label>
        <select name="section" id="section">
            <option value="">Select Section</option>
            <?php while ($row = $sectionResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['section']; ?>" <?php if ($section == $row['section']) echo 'selected'; ?>>
                    <?php echo $row['section']; ?>
                </option>
            <?php } ?>
        </select>

        <label for="subject">Subject:</label>
        <select name="subject" id="subject">
            <option value="">Select Subject</option>
            <?php while ($row = $subjectResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['subject']; ?>" <?php if ($subject == $row['subject']) echo 'selected'; ?>>
                    <?php echo $row['subject']; ?>
                </option>
            <?php } ?>
        </select>

        <label for="date">Date:</label>
        <select name="date" id="date">
            <option value="">Select Date</option>
            <?php while ($row = $dateResult->fetch_assoc()) { ?>
                <option value="<?php echo $row['date']; ?>" <?php if ($date == $row['date']) echo 'selected'; ?>>
                    <?php echo $row['date']; ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit">Filter</button>
    </form>
</div>

<div class="attendance-container">
    <h3>Filtered Attendance Records</h3>
    <table>
        <thead>
            <tr>
                <th>Roll No</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
                <th>Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($attendanceResult->num_rows > 0) {
                $addedRolls = [];
                while ($row = $attendanceResult->fetch_assoc()) {
                    $rollno = $row['rollno'];

                    // Calculate attendance percentage for each student-subject combo
                    if (!in_array($rollno, $addedRolls)) {
                        $addedRolls[] = $rollno;

                        $filterCondition = "rollno = '$rollno'";
                        if ($subject) {
                            $filterCondition .= " AND subject = '$subject'";
                        }

                        $totalQuery = "SELECT COUNT(*) as total FROM attendance WHERE $filterCondition";
                        $presentQuery = "SELECT COUNT(*) as present FROM attendance WHERE $filterCondition AND status = 'Present'";

                        $totalRes = $conn->query($totalQuery);
                        $presentRes = $conn->query($presentQuery);

                        $total = $totalRes->fetch_assoc()['total'];
                        $present = $presentRes->fetch_assoc()['present'];
                        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
                    }

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['rollno']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>" . $percentage . "%</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div style="text-align: center; margin: 20px;">
    <a href="/dept/hod/hod.php" class="back-button" style="padding: 10px 20px; background-color:rgb(228, 106, 106); color: white; text-decoration: none; border-radius: 5px;">⬅ Back to Dashboard</a>
</div>

<footer class="footer">
    <div class="footer-left">
        <p>&copy; <?php echo date("Y"); ?> Computer Science and Engineering(Artificial Intelligence) Department</p>
    </div>
    <div class="footer-right">
        <p>Developed & Designed by Tushar Dhaker || Puneet Agrawal</p>
    </div>
</footer>
</body>
</html>
