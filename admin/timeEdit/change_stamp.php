<?php
date_default_timezone_set('Atlantic/Reykjavik');
require "../admin_functions.php";
include '../SqlStatements.php';
if (sessionCheck()) {
    $stampId = $_POST['sid'];
    $defaultTime = $_POST['dtime'];
    $time = date("Y-m-d H:i:s", $_POST['time']);
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::MODIFY_STAMP, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':stamp', $time, PDO::PARAM_STR);
        $stmt->bindParam(':stampid', $stampId, PDO::PARAM_INT);
        $stmt->execute();
        logTransaction($dbh, $stampId, "CHANGE", $defaultTime, $time);
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    }
    $dbh = null;
}
