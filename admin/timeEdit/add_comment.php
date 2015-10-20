<?php
require '../admin_functions.php';
include '../SqlStatements.php';
if (sessionCheck()) {
    $stampid = $_POST['stampid'];
    $comment = $_POST['comment'];
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::INSERT_STAMP_COMMENT, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':stampid', $stampid, PDO::PARAM_INT);
        $stmt->bindParam(':tscomment', $comment, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    }
    $dbh = null;
}
