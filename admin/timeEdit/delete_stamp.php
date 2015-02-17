<?php
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $stampId = $_POST['sid'];
    $defaultTime = $_POST['dtime'];
    mysqli_query($sqlConnection, "DELETE FROM timestamp_list WHERE stamp_id = $stampId");
    logTransaction($sqlConnection, $stampId, "DELETE", $defaultTime, 0);
    mysqli_close($sqlConnection);
}
