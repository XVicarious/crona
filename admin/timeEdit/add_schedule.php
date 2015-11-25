<?php
require "../admin_functions.php";
include "../SqlStatements.php";
if (sessionCheck()) {
    $userId = $_POST['userId'];
    $year   = $_POST['year'];
    $week   = $_POST['week'];
    $day    = $_POST['day'];
    $dbh = createPDO();
    $stmt = $dbh->prepare(SqlStatements::INSERT_SCHEDULE);
    $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':syear', $year, PDO::PARAM_INT);
    $stmt->bindParam(':sweek', $week, PDO::PARAM_INT);
    $stmt->bindParam(':sday', $day, PDO::PARAM_INT);
    $stmt->execute();
    $dbh = null;
}
