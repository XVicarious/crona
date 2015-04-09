<?php
require 'admin_functions.php';
$sqlConnection = createSql();
if (sessionCheck()) {
    $query = 'SELECT change_time,change_userid,change_from_to, (SELECT user_name FROM employee_list WHERE employee_list.user_id = change_list.change_userid) AS username
              FROM change_list
              ORDER BY change_time DESC';
    $queryResult = mysqli_query($sqlConnection, $query);
    $qarray = [];
    while (list($changeTime,$changeId,$changeArray,$username) = mysqli_fetch_row($queryResult)) {
        $unserialzed = unserialize($changeArray);
        array_push($qarray, ['modtime'=>$changeTime, 'userchanged'=>$username, 'from'=>$unserialzed[2], 'to'=>$unserialzed[3]]);
    }
    echo json_encode($qarray);
}
