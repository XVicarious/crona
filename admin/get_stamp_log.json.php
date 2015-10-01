<?php
require 'admin_functions.php';
$sqlConnection = createSql();
if (sessionCheck()) {
    $query = 'SELECT user_id_stamp,timestamp_list.datetime, (SELECT user_name FROM employee_list
              WHERE employee_list.user_id = timestamp_list.user_id_stamp) AS username
              FROM timestamp_list
              ORDER BY tsl_stamp DESC';
    $queryResult = mysqli_query($sqlConnection, $query);
    $qarray = [];
    while (list($userId,$stamp,$username) = mysqli_fetch_row($queryResult)) {
        array_push($qarray, ['user'=>$username, 'time'=>$stamp]);
    }
    echo json_encode($qarray);
}
