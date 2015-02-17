<?php

function sessionCheck()
{
    session_start();
    $lastAction = $_SESSION['lastAction'];
    if ($lastAction + 10 * 60 < time()) {
        session_destroy();
        echo '<script>$(location).attr("href","http://xvss.net/time?timeout=1")</script>';
        return false;
    }
    $_SESSION['lastAction'] = time();
    return true;
}

function getPermissions($sqlConnection)
{
    if (session_status() === PHP_SESSION_NONE) {
        sessionCheck();
    }
    $adminId = $_SESSION['userId'];
    if (isset($adminId)) {
        $permissionResult = mysqli_query($sqlConnection, "SELECT user_admin_perms FROM employee_list
                                                          WHERE user_id = $adminId");
        list($sa_permissions) = mysqli_fetch_row($permissionResult);
        // permission "all" is a:1:{i:0;s:3:"all";}
        return unserialize($sa_permissions);
    }
    return [];
}

function createSql()
{
    $sql_server = 'localhost';
    $sql_username = 'bmaurer_pciven';
    $sql_password = '3al12of4ut25';
    $sql_database = 'bmaurer_hhemployee';
    return mysqli_connect($sql_server, $sql_username, $sql_password, $sql_database);
}

function logTransaction($sqlConnection, $stampId, $type, $originalValue, $newValue)
{
    $transactionArray = [$stampId, $type, $originalValue, $newValue];
    $transactionArray = serialize($transactionArray);
    $adminId = $_SESSION['userId'];
    $query = "INSERT INTO change_list (change_userid,change_from_to) VALUES ($adminId, '$transactionArray');";
    mysqli_query($sqlConnection, $query);
}

function findExceptions($sqlConnection)
{
    //$counter = mysqli_query($sqlConnection, 'SELECT COUNT(*) AS id FROM employee_list');
    //$n = mysqli_fetch_array($counter);
    //$count = $n['id'];
    // Exceptions are for only missing punches right now
    $timestamp_list = mysqli_query($sqlConnection, 'SELECT stamp_id,user_id_stamp,datetime FROM timestamp_list');
    $userStamps = [];
    if (mysqli_num_rows($timestamp_list) !== 0) {
        while (list($stampId, $userId, $datetime) = mysqli_fetch_row($timestamp_list)) {
            if ($userStamps[$userId] === null) {
                $userStamps[$userId] = [];
            }
            array_push($userStamps[$userId], [$stampId, $datetime]);
        }
    }
    foreach ($userStamps as $a_stamp) {
        if (count($a_stamp) % 2) {
            // There is an inconsistency
        }
    }
    return count($userStamps[1]);
}
