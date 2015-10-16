<?php
require "../admin_functions.php";
include '../SqlStatements.php';
if (sessionCheck()) {
    $stampId = $_POST['sid'];
    $defaultTime = $_POST['dtime'];
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::DELETE_STAMP_BY_ID);
        $stmt->bindParam(':stampid', $stampId, PDO::PARAM_INT);
        $stmt->execute();
        logTransaction($sqlConnection, $stampId, "DELETE", $defaultTime, 0);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
    $dbh = null;
}
