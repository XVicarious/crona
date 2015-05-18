<?php
date_default_timezone_set('Atlantic/Reykjavik');
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $dtime = $_POST['date'];
    $date = date("Y-m-d H:i:s", $dtime);
    $userId = $_POST['user'];
    mysqli_query($sqlConnection, "INSERT INTO timestamp_list (user_id_stamp,datetime) VALUES ($userId,'$date')");
    logTransaction($sqlConnection, '?', 'ADD', 0, $date);
    mysqli_close($sqlConnection);
}
