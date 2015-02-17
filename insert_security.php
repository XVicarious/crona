<?php
session_start();
$a_sec_1 = [$_POST['s1'],sha1($_POST['s1i'])];
$a_sec_2 = [$_POST['s2'],sha1($_POST['s2i'])];
$a_sec_3 = [$_POST['s3'],sha1($_POST['s3i'])];
$sa_sec_1 = serialize($a_sec_1);
$sa_sec_2 = serialize($a_sec_2);
$sa_sec_3 = serialize($a_sec_3);
$userId = $_SESSION['userId'];
require 'admin/admin_functions.php';
$sqlConnection = createSql();
$query = "INSERT INTO employee_security (sec_user_id,sec_1,sec_2,sec_3) VALUES ($userId,'$sa_sec_1','$sa_sec_2','$sa_sec_3')";
mysqli_query($sqlConnection, $query);
mysqli_close($sqlConnection);
