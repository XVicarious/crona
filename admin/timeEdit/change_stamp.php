<?php
date_default_timezone_set('Atlantic/Reykjavik');
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $stampId = $_POST['sid'];
    $defaultTime = $_POST['dtime'];
    $time = date("Y-m-d H:i:s", $_POST['time']);
    mysqli_query($sqlConnection, "UPDATE timestamp_list SET tsl_stamp = '$time'
                                  WHERE stamp_id = $stampId");
    logTransaction($sqlConnection, $stampId, "CHANGE", $defaultTime, $time);
    mysqli_close($sqlConnection);
}
