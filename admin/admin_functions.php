<?php

/**
 * @return bool - if the session is all good, true; if it timed out, destroy it
 */
function sessionCheck()
{
    if (!isset($_SESSION)) {
        session_start();
    }
    $lastAction = $_SESSION['lastAction'];
    $timeoutThreshold = readSetting(['applicationTimeout']);
    $timeoutThreshold = intval($timeoutThreshold);
    if ($lastAction + ($timeoutThreshold * 60) < time()) {
        session_destroy();
        echo '<script src="../js/lib/jquery.js"></script>
              <script>$(location).attr("href","http://xvss.net/time?timeout=1")</script>';
        return false;
    }
    $_SESSION['lastAction'] = time();
    return true;
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

/**
 * @param PDO $databaseConnection - connection to the database
 * @param $stampId - what stamp was modified
 * @param $type - what kind of modification it was
 * @param $originalValue - what the value was before
 * @param $newValue - what the value is now
 * @return bool - if the transaction logged successfully, true; if an error occurred, false
 */
function logTransaction(PDO &$databaseConnection, $stampId, $type, $originalValue, $newValue)
{
    // todo: fix this mess, using a serialized array.  Disgusting.
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
        $stringLength -= 16;
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

/**
 * @param array $settings - an array of the setting to retrieve, in the order they appear
 * @param $file - the settings file, usually this is just settings.json
 * @return array $settingsJson - can be anything... I didn't plan this out very well.  I guess array.  Yeah, that.
 * ex. to get "timeout" "late", ['stampThresholds','timeOut','late']
 */
function readSetting(array $settings, $file = '/home2/bmaurer/public_html/xvss.net/devel/time/admin/settings.json')
{
    $jsonString = file_get_contents($file);
    $settingsJson = json_decode($jsonString, true);
    foreach ($settings as $setting) {
        $settingsJson = $settingsJson[$setting];
    }
    return $settingsJson;
}
