<?php
require "../admin_functions.php";
include "../SqlStatements.php";
if (sessionCheck()) {
    $userId = $_POST['userId'];
    $year   = $_POST['year'];
    $week   = $_POST['week'];
    $day    = $_POST['day'];
    $format = '%Y/%V/%u %H:%M:%S';
    $dateString = $year.'/'.$week.'/'.$day.' 00:00:00';
    error_log('$dateString: '.$dateString, 0);
    $dateA = strptime($dateString, $format);
    error_log(json_encode($dateA, true), 0);
    $dateAString = ($dateA['tm_year'] + 1900).'-'.($dateA['tm_mon'] + 1).'-'.$dateA['tm_mday'].' 00:00:00';
    error_log($dateAString, 0);
    $unixdate = DateTime::createFromFormat('Y-m-d G::i:s', $dateAString);
    //$unixdate = $unixdate->format('U');
    $unixdate = date_format($unixdate, 'U');
    //$dateTime =
    $dbh = createPDO();
    $stmt = $dbh->prepare(SqlStatements::INSERT_SCHEDULE);
    $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':sched', $unixdate, PDO::PARAM_INT);
    $stmt->execute();
    $dbh = null;
}
