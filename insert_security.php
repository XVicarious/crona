<?php
session_start();
$question1 = $_POST['s1'];
$answer1 = sha1(strtolower($_POST['s1i']));
$question2 = $_POST['s2'];
$answer2 = sha1(strtolower($_POST['s2i']));
$question3 = $_POST['s3'];
$answer3 = sha1(strtolower($_POST['s3i']));
$userId = $_SESSION['userId'];
require 'admin/admin_functions.php';
$sqlConnection = createSql();
$query = "INSERT INTO employee_questions (eque_number, eque_answer, eque_user) VALUES ($question1, '$answer1', $userId),
          ($question2, '$answer2', $userId), ($question3, '$answer3', $userId)";
echo "<script>console.log('$query')</script>";
mysqli_query($sqlConnection, $query);
mysqli_close($sqlConnection);
