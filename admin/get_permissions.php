<?php
require "admin_functions.php";
include 'SqlStatements.php';
if (sessionCheck()) {
    $userId = $_SESSION['userId'];
    $dbh = createPDO();
    try {
        $stmt = $dbh->prepare(SqlStatements::GET_CHECK_PERMISSIONS, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    } catch (Exception $e) {
        javascriptLog('Failed: '.$e->getMessage());
    }
    $permissionArray = [];
    /*while(list($companyCode, $departmentId) = mysqli_fetch_row($result)) {
        array_push($permissionArray, [$companyCode, $departmentId]);
    }
    echo json_encode($permissionArray);
    */
    $dbh = null;
}
