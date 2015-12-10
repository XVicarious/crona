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
            schErr($employee, 'Starting loop for $result');
            schErr($employee, json_encode($scheduleDay, true));
            $last = end($fixedSchedule);
            schErr($employee, '$last is '.$last);
            if (!empty($last)) {
                schErr($employee, '$last is not null (end($fixedSchedule))');
                if ($scheduleDay['schedule_year'] === $last['schedule_year'] &&
                    $scheduleDay['schedule_week'] === $last['schedule_week']) {
                    schErr($employee, 'week and year match $last and this');
                    // to check if there is more than one day between the previous day and the day we are inputting
                    $absoluteDayGap = abs($scheduleDay['schedule_day'] - $last['schedule_day']);
                    schErr($employee, '$scheduleDay[\'schedule_day\']='.$scheduleDay['schedule_day']);
                    schErr($employee, '$last[0][\'schedule_day\']='.$last['schedule_day']);
                    if ($scheduleDay['schedule_day'] === $last['schedule_day']) {
                        schErr($employee, 'the days match as well');
                        $fixedScheduleCount = count($fixedSchedule) - 1;
                        error_log('pushing to already created array', 0);
                        array_push($fixedSchedule, $scheduleDay);
                    } elseif ($absoluteDayGap > 1) {
                        schErr($employee, 'The days don\'t match and the gap is greater than 1, it is '.$absoluteDayGap);
                        $whileGap = $absoluteDayGap;
                        while ($whileGap > 1) {
                            schErr($employee, 'pushing a schedule day in with $whileGap='.$whileGap);
                            array_push($fixedSchedule, ['schedule_day' => $last['schedule_day']+$whileGap]);
                            $whileGap--;
                        }
                        schErr($employee, '$whileGap under control...  adding the stamp that needs adding now');
                        array_push($fixedSchedule, $scheduleDay);
                    } else {
                        schErr($employee, 'the days don\'t match, but the gap is only 1 so we are fine');
                        array_push($fixedSchedule, $scheduleDay);
                    }
                } else {
                    array_push($fixedSchedule, $scheduleDay);
                }
            } else {
                $whileGap = $scheduleDay['schedule_day'];
                $currentWhileDay = 1;
                while ($whileGap > $currentWhileDay) {
                    array_push($fixedSchedule, ['schedule_day' => $currentWhileDay++]);
                }
                array_push($fixedSchedule, $scheduleDay);
            }
        }
        $dayArray = [1,2,3,4,5,6,7];
        foreach ($dayArray as $weekDay) {
            if (!array_search($weekDay, array_column($fixedSchedule, 'schedule_day'))) {
                array_push($fixedSchedule, ['schedule_day' => $weekDay]);
            }
        }
        usort($fixedSchedule, 'csort');
        array_unshift($fixedSchedule, ['year'=>$year, 'week'=>$week, 'userid'=>$employee]);
        echo json_encode($fixedSchedule, JSON_NUMERIC_CHECK);
    } catch (PDOException $e) {
        error_log($e->getMessage(), 0);
    } catch (Exception $e) {
        error_log($e->getMessage(), 0);
        die();
    }
}

function schErr($user,$message)
{
    error_log('get_schedule.php id='.$user.' : '.$message, 0);
}

function csort($left, $right, $column = 'schedule_day')
{
    return $left[$column] > $right[$column];
}
