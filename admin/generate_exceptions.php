<?php
require 'admin_functions.php';
include 'SqlStatements.php';
if (sessionCheck()) {
    $generateMode = $_POST['exceptionMode'];
    if ($generateMode === 'gather') {
        error_log('Gather information here', 0);
        $dbh = createPDO();
        $userId = $_SESSION['userId'];
        // First we need to get the time that each place the user controls was updated
        try {
            $statement = $dbh->prepare(
                SqlStatements::GET_LAST_EXCEPTION_GENERATION_BY_USER,
                [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]
            );
            $statement->bindParam(':userid', $userId, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            $toUpdate = [];
            // Now we want to filter them so we only get ones that were updated 3 or more hours ago
            foreach ($result as $lastException) {
                $exh_time = $lastException['exh_time'];
                if (!$exh_time) {
                    $exh_time = 0;
                }
                $elapsedTime = time() - $exh_time;
                if ($elapsedTime >= 10800) {
                    if (array_key_exists($lastException['exh_property'], $toUpdate)) {
                        array_push($toUpdate['exh_property'], $lastException['exh_department']);
                    } else {
                        array_push($toUpdate, [$lastException['exh_property'] => [$lastException['exh_department']]]);
                    }
                }
            }
            // We will only want to fetch data from the last 14 days, or 2 weeks

        } catch (PDOException $e) {
            error_log($e->getMessage(), 0);
        }
    } elseif ($generateMode === 'generate') {
        error_log('Generate exceptions here', 0);
    } else {
        error_log('Invalid or no exceptionMode given: ' . $generateMode);
    }
}
