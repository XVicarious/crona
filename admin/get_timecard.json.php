<?php
$startTime = microtime(false);
date_default_timezone_set('America/New_York'); // todo: make timezone configurable
require 'admin_functions.php';
include 'SqlStatements.php';
$dateFormat = 'Y-m-d';
$timeFormat24 = 'H:i:s';
$timeFormat12 = 'h:i:s a';
$dateTimeFormat24 = $dateFormat.' '.$timeFormat24;
if (sessionCheck()) {
    $employeeId = intval($_POST['employee']);
    $range = $_POST['range'];
    // todo: this might be a tad screwy because of timezones
    $day = date('w') + 1;
    $date0 = date($dateFormat, strtotime("-$day days"));
    $date1 = date($dateFormat, strtotime('+'.(7-$day).' days'));
    if ($range === 'last') {
        $date0 = date($dateTimeFormat24, strtotime("-$day days -1 weeks 00:00:00"));
        $date1 = date($dateTimeFormat24, strtotime('-'.($day + 1).' days 23:59:59'));
    } elseif ($range === 'next') {
        $date0 = date($dateTimeFormat24, strtotime("-$day days +1 weeks"));
        $date1 = date($dateTimeFormat24, strtotime('+'.(6-$day).' days +1 weeks 23:59:59'));
    } elseif ($range === 'specificDate') {
        $date0 = date($dateTimeFormat24, $_POST['date0']);
        $date1 = date($dateTimeFormat24, $_POST['date1']);
    }
    $dbh = createPDO();
    $timestamps = [];
    try {
        $stmt = $dbh->prepare(SqlStatements::GET_STAMPS_EMPLOYEE_RANGE, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':userid', $employeeId, PDO::PARAM_INT);
        $stmt->bindParam(':date0', $date0, PDO::PARAM_STR);
        $stmt->bindParam(':date1', $date1, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dateEDT = new DateTimeZone('America/New_York');
        $offsetSeconds = $dateEDT->getOffset(new DateTime('now'));
        $stamps = [];
        $n = 1;
        $days = strtotime($date1) - strtotime($date0);
        $days = ($days / 86400) + 1;
        while ($n < $days) {
            $dateOfWeek = date($dateFormat, strtotime("$date0 +$n days"));
            array_push($timestamps, ['date'=>$dateOfWeek]);
            $n++;
        }
        $datesCount = count($timestamps);
        foreach ($result as $timestamp) {
            for ($i = 0; $i < $datesCount; ++$i) {
                $tslTime = strtotime($timestamp['tsl_stamp']) + $offsetSeconds;
                $thisDay = date($dateFormat, $tslTime);
                $thisTime = date($timeFormat12, $tslTime);
                $thisUnix = date('U', $tslTime);
                if ($timestamps[$i]['date'] === $thisDay || in_array($timestamp['stamp_partner'], $stamps)) {
                    // push things for the current day.  they can either be actually for this day, or reference stamps from it
                    $timestampsCount = count($timestamps) - 1;
                    array_push($timestamps[$i], [$timestamp['stamp_id'], $thisUnix, $timestamp['stamp_special'], $timestamp['stamp_department'], $timestamp['stamp_partner'], $timestamp['tsl_comment']]);
                }
            }
            array_push($stamps, $timestamp['stamp_id']);
        }
        /*
         * Then do some tricky bullshit that I don't even remember how it works
         * -- Brian
         * ps. its not in this version...
         * pss.  im not even sure it was needed
         */
        try {
            $stmt = $dbh->prepare(SqlStatements::GET_USER_NAME_DATE, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $stmt->bindParam(':userid', $employeeId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // this all seems woefully inefficient.
            $timestamps['USER_INFO'] = ['user_first'=>'','user_last'=>'','user_start'=>'','user_id'];
            $timestamps['USER_INFO']['user_first'] = $result[0]['user_first'];
            $timestamps['USER_INFO']['user_last'] = $result[0]['user_last'];
            $timestamps['USER_INFO']['user_start'] = $result[0]['user_start'];
            $timestamps['USER_INFO']['user_id'] = $employeeId;
            $dbh = null;
        } catch (Exception $e) {
            error_log('Failed: '.$e->getMessage());
        }
    } catch (Exception $e) {
        error_log('Failed: '.$e->getMessage());
    }
    $date = new DateTime();
    $date->setTimestamp(strtotime($date0));
    $year = intval($date->format('Y'));
    $week = intval($date->format('W')) + 1;
    $runningTotal = 0;
    $timestampsCount = count($timestamps);
    $maxStamps = 0;
    foreach ($timestamps as $t) {
        $countT = count($t);
        if ($countT > $maxStamps) {
            $maxStamps = $countT;
        }
    }
    foreach ($timestamps as &$timestamp) {
        $timeIn = [];
        $timeOut = [];
        foreach ($timestamp as $key => $stamp) {
            if (is_array($stamp)) {
                if ($key % 2) {
                    array_push($timeOut, $stamp[1]);
                } else {
                    array_push($timeIn, $stamp[1]);
                }
            }
        }
        $timeTotal = 0;
        for ($i = 0; $i < count($timeOut); ++$i) {
            $timeTotal += ($timeOut[$i] - $timeIn[$i]);
        }
        $timestamp['totalTime'] = $timeTotal;
    }
    echo json_encode($timestamps);
}
$endTime = microtime(false);
$totalTime = $endTime - $startTime;
$memory = memory_get_usage(true);
if ($totalTime < 0 || $totalTime >= 1) {
    error_log(__FILE__.' executed in '.$totalTime.' by userId:'.$_SESSION['userId'].' using '.$memory.'b', 0);
}
