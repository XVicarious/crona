<?php

function sessionCheck()
{
    if (!isset($_SESSION)) {
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
    $sql_password = '3al12of4ut25';
    $sql_database = 'bmaurer_hhemployee';
    return mysqli_connect($sql_server, $sql_username, $sql_password, $sql_database);
}

function createPDO()
{
    $sql_username = 'bmaurer_pciven';
    $sql_password = '3al12of4ut25';
    try {
        return new PDO('mysql:host=localhost;dbname=bmaurer_hhemployee', $sql_username, $sql_password);
    } catch (Exception $e) {
        die('Unable to connect: ' . $e->getMessage());
    }
}

/*function logTransaction($sqlConnection, $stampId, $type, $originalValue, $newValue)
{
    $transactionArray = [$stampId, $type, $originalValue, $newValue];
    $transactionArray = serialize($transactionArray);
    $adminId = $_SESSION['userId'];
    $query = "INSERT INTO change_list (change_userid,change_from_to) VALUES ($adminId, '$transactionArray');";
    mysqli_query($sqlConnection, $query);
}*/

function logTransaction(PDO &$databaseConnection, $stampId, $type, $originalValue, $newValue)
{
    $transactionArray = [$stampId, $type, $originalValue, $newValue];
    $transactionArray = serialize($transactionArray);
    $adminId = $_SESSION['userId'];
    try {
        $stmt = $databaseConnection->prepare(SqlStatements::LOG_TRANSACTION);
        $stmt->bindParam(':adminid', $adminId, PDO::PARAM_INT);
        $stmt->bindParam(':transaction', $transactionArray, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        return false;
    }
    return true;
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

function pre($string)
{
    echo '<pre>';
    print_r($string);
    echo '</pre>';
    return;
}
