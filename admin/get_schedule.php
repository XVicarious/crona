<?php
require "admin_functions.php";
include 'SqlStatements.php';
if (sessionCheck()) {
    // Use this to select the starting page
    // todo: fringe cases where the year changes in the middle of the week.  These dates will not be fetched!
    $employee = $_POST['userId'];
    $year = $_POST['year'];
    $week = $_POST['week'];
    $dbh = createPDO();
    $result = null;
    try {
        $stmt = $dbh->prepare(SqlStatements::GET_SCHEDULE, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':userid', $employee, PDO::PARAM_INT);
        $stmt->bindParam(':sweek', $week, PDO::PARAM_INT);
        $stmt->bindParam(':syear', $year, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $fixedSchedule = [];
        foreach ($result as $scheduleDay) {
            $last = end($fixedSchedule);
            if ($last !== null) {
                if ($scheduleDay['schedule_year'] === $last[0]['schedule_year'] &&
                    $scheduleDay['schedule_week'] === $last[0]['schedule_week'] &&
                    $scheduleDay['schedule_day'] === $last[0]['schedule_day']) {
                    $fixedScheduleCount = count($fixedSchedule) - 1;
                    error_log('pushing to already created array', 0);
                    array_push($fixedSchedule[$fixedScheduleCount], $scheduleDay);
                } else {
                    array_push($fixedSchedule, $scheduleDay);
                }
            } else {
                $last = [];
                array_push($last, $scheduleDay);
            }
        }
        array_unshift($result, ['year'=>$year, 'week'=>$week, 'userid'=>$employee]);
        echo json_encode($result, JSON_NUMERIC_CHECK);
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        die();
    }
}
