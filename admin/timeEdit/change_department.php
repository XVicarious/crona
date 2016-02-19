<?php
require "../admin_functions.php";
require '../SqlStatements.php';
if (sessionCheck()) {
    $stampId = $_POST['sid'];
    $department = $_POST['modifier'];
    $dbh = createPDO();
    try {
        $success = false;
        $stmt = $dbh->prepare(SqlStatements::CHANGE_DEPARTMENT);
        $stmt->bindParam(':department', $department, PDO::PARAM_INT);
        $stmt->bindParam(':stampID', $stampId, PDO::PARAM_INT);
        $stmt->execute();
        // todo: get old department, if any
        logTransaction($dbh, $stampId, 'DEPT', 0, $department);
        $success = true;
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
    } finally {
        $dbh = null;
        if (!$success) {
            die();
        }
    }
}
