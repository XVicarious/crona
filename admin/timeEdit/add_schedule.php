<?php
require "../admin_functions.php";
include "../SqlStatements.php";
if (sessionCheck()) {
    $userId = $_POST['userId'];
    $unixStamp   = $_POST['unix'];
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::INSERT_SCHEDULE);
        $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':sched', $unixStamp, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    $dbh = null;
}
