<?php
date_default_timezone_set('Atlantic/Reykjavik');
require "../admin_functions.php";
include '../SqlStatements.php';
if (sessionCheck()) {
    $dtime = $_POST['date'];
    $date = date("Y-m-d H:i:s", $dtime);
    $userId = $_POST['user'];
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::INSERT_NEW_STAMP, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        logTransaction($sqlConnection, '?', 'ADD', 0, $date);
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    }
}
