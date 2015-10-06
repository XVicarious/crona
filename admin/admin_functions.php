<?php

function sessionCheck()
{
    if (!isset($_SESSION)) {
        error_log('No session, starting...', 0);
        session_start();
    }
    $lastAction = $_SESSION['lastAction'];
    if ($lastAction + 20 * 60 < time()) {
        session_destroy();
        echo '<script src="../js/lib/jquery.js"></script><script>$(location).attr("href","http://xvss.net/time?timeout=1")</script>';
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
    $sql_password = '***REMOVED***';
    $sql_database = 'bmaurer_hhemployee';
    return mysqli_connect($sql_server, $sql_username, $sql_password, $sql_database);
}

function createPDO()
{
    $sql_username = 'bmaurer_pciven';
    $sql_password = '***REMOVED***';
    try {
        return new PDO('mysql:host=localhost;dbname=bmaurer_hhemployee', $sql_username, $sql_password);
    } catch (Exception $e) {
        die('Unable to connect: ' . $e->getMessage());
    }
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
    $timestamp_list = mysqli_query($sqlConnection, 'SELECT stamp_id,user_id_stamp,tsl_stamp FROM timestamp_list');
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

function randomSalt($useSpecial = true, $len = 8)
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()_+-=";
    $stringLength = strlen($chars) - 1;
    if (!$useSpecial) {
        $stringLength = strlen($chars - 16) - 1;
    }
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $chars[rand(0, $stringLength)];
    }
    return $str;
}

function randomSalt16()
{
    return randomSalt(true, 16);
}

function generateUsername($sqlConnection, $baseUsername, $number = 0)
{
    $username = $number ? $baseUsername . $number : $baseUsername;
    $result = mysqli_query($sqlConnection, "SELECT user_name FROM employee_list WHERE user_name = '$username'");
    if (mysqli_num_rows($result) !== 0) {
        $username = generateUsername($sqlConnection, $baseUsername, ++$number);
    }
    return strtolower($username);
}
