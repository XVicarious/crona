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
        $dayArray = ['1','2','3','4','5','6','0'];
        foreach ($dayArray as $weekDay) {
            if (array_search($weekDay, array_column($result, 'schedule_day')) === false) {
                array_push($result, ['schedule_day' => $weekDay]);
            }
        }
        usort($result, 'csort');
        array_unshift($result, ['year'=>$year, 'week'=>$week, 'userid'=>$employee]);
        echo json_encode($result, JSON_NUMERIC_CHECK);
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        die();
    }
}

function schErr($user, $message)
{
    error_log('get_schedule.php id='.$user.' : '.$message, 0);
}

function csort($left, $right, $column = 'schedule_day')
{
    return $left[$column] > $right[$column];
}
