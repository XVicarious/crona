<?php
// todo: update SQL
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $stampId = $_POST['sid'];
    $department = $_POST['modifier'];
    mysqli_query($sqlConnection, "UPDATE timestamp_list SET stamp_department = $department WHERE stamp_id = $stampId");
    // todo: get old department, if any
    logTransaction($sqlConnection, $stampId, "DEPT", 0, $department);
    mysqli_close($sqlConnection);
}
