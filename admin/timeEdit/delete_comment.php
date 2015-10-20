<?php
require '../admin_functions.php';
include '../SqlStatements.php';
if (sessionCheck()) {
    $stampid = $_POST['stampid'];
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::DELETE_COMMENT, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':stampid', $stampid, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    }
    $dbh = null;
}
