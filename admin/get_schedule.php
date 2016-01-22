<?php
require "admin_functions.php";
include 'SqlStatements.php';
if (sessionCheck()) {
    // Use this to select the starting page
    // todo: fringe cases where the year changes in the middle of the week.  These dates will not be fetched!
    $employee = $_POST['userId'];
    $unixStart = $_POST['ustart'];
    $unixEnd = $_POST['uend'];
    $dbh = createPDO();
    $result = null;
    try {
        $stmt = $dbh->prepare(SqlStatements::GET_SCHEDULE_2, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':userid', $employee, PDO::PARAM_INT);
        $stmt->bindParam(':ustart', $unixStart, PDO::PARAM_INT);
        $stmt->bindParam(':uends', $unixEnd, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $fixedSchedule = [];
        foreach ($result as $item) {
            if ($item['schedule_pair'] === null) {
                error_log('null', 0);
                array_push($fixedSchedule, [$item]);
            } else {
                $pairIndex = searchInArray($item['schedule_pair'], $fixedSchedule, 'schedule_id');
                $indexPair = searchInArray($item['schedule_id'], $fixedSchedule, 'schedule_pair');
                if (!($pairIndex === false)) {
                    error_log('$pairIndex '.$pairIndex.' '.json_encode($item, true), 0);
                    array_push($fixedSchedule[$pairIndex], $item);
                } elseif (!($indexPair === false)) {
                    error_log('$indexPair '.$indexPair.' '.json_encode($item, true), 0);
                    array_push($fixedSchedule[$indexPair], $item);
                } else {
                    error_log('new '.json_encode($item, true), 0);
                    array_push($fixedSchedule, [$item]);
                }
            }
        }
        //$dayArray = ['1','2','3','4','5','6','0'];
        //foreach ($dayArray as $weekDay) {
        //    if (array_search($weekDay, array_column($result, 'schedule_day')) === false) {
        //        array_push($result, ['schedule_day' => $weekDay]);
        //    }
        //}
        //usort($result, 'csort');
        array_unshift($fixedSchedule, ['userid'=>$employee]);
        echo json_encode($fixedSchedule, JSON_NUMERIC_CHECK);
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

function searchInArray($needle, $haystack, $key, $strict = false)
{
    error_log($needle.' '.json_encode($haystack, true).' '.$key);
    foreach ($haystack as $index => $item) {
        if (($strict ? $item[0][$key] === $needle : $item[0][$key] == $needle)) {
            error_log($index, 0);
            return $index;
        }
    }
    error_log('not found', 0);
    return false;
}
