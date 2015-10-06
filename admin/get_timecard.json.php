<?php
date_default_timezone_set('America/New_York'); // todo: make timezone configurable
require 'admin_functions.php';
$sqlConnection = createSql();
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
        $date0 = date($dateTimeFormat24, strtotime("-$day days -1 weeks"));
        $date1 = date($dateTimeFormat24, strtotime('-'.($day).' days 23:59:59'));
    } elseif ($range === 'next') {
        $date0 = date($dateTimeFormat24, strtotime("-$day days +1 weeks"));
        $date1 = date($dateTimeFormat24, strtotime('+'.(6-$day).' days +1 weeks 23:59:59'));
    } elseif ($range === 'specificDate') {
        $date0 = date($dateTimeFormat24, $_POST['date0']);
        $date1 = date($dateTimeFormat24, $_POST['date1']);
    } elseif ($range === 'w2d') {
        // todo: is this REALLY needed?
        $date0 = date($dateTimeFormat24, strtotime('monday 00:00:00'));
        $date1 = date($dateTimeFormat24, strtotime('today 23:59:59'));
    }
    $query = "SELECT stamp_id,tsl_stamp,stamp_special,stamp_department,stamp_partner
              FROM timestamp_list
              WHERE user_id_stamp = $employeeId
              AND tsl_stamp BETWEEN '$date0' AND '$date1'
              ORDER BY tsl_stamp";
    $queryResult = mysqli_query($sqlConnection, $query);
    if ($queryResult === false) {
        // If the query was somehow broken, just die.
        return;
    }
    $timestamps = [];
    $stamps = [];
    while (list($stampId, $timestamp, $modifier, $department, $partnerId) = mysqli_fetch_row($queryResult)) {
        $lastTimestamp = end($timestamps);
        // We need the offset from the current timezone to GMT
        $dateEDT = new DateTimeZone('America/New_York'); // todo: make timezone configurable
        $offsetSeconds = $dateEDT->getOffset(new DateTime('now'));
        $thisDay = date($dateFormat, strtotime($timestamp) + $offsetSeconds);
        $thisTime = date($timeFormat12, strtotime($timestamp) + $offsetSeconds);
        $thisUnix = date('U', strtotime($timestamp) + $offsetSeconds);
        if ($lastTimestamp['date'] === $thisDay || in_array($partnerId, $stamps)) {
            // push things in for this day.  They can be for this day, or reference it
            $timestampsCount = count($timestamps) - 1;
            array_push($timestamps[$timestampsCount], [$stampId, $thisUnix, $modifier, $department, $partnerId]);
        } else {
            // if this is a new day (or it doesn't reference the previous day) then start a new day
            array_push($timestamps, ['date'=>$thisDay, [$stampId, $thisUnix, $modifier, $department, $partnerId]]);
        }
        //  We need the stampIds to tell if the stamps reference one another
        array_push($stamps, $stampId);
    }
    // todo: breaks could be an issue here!
    // fixme: If there are no stamps, array_splice errors out!
    /*
    if (!in_array($timestamps[0][0][4], $stamps)) {
        // Remove the first entry of the array if it isn't right!
        array_splice($timestamps[0], 0, 1);
        if (empty($timestamps[0])) {
            // todo: this doesn't work properly because of [date]
            // If we left the first array empty, get rid of it
            array_splice($timestamps, 0, 1);
        }
    }
    */
    // todo: look into sunday of next week for matching stamps also
    $employeeNameQuery = "SELECT user_first, user_last, user_start
                          FROM employee_list
                          WHERE user_id = $employeeId";
    $nameResult = mysqli_query($sqlConnection, $employeeNameQuery);
    if (mysqli_num_rows($nameResult) !== 0) {
        list($userFirst, $userLast, $userStart) = mysqli_fetch_row($nameResult);
    } else {
        $userFirst = 'null';
        $userLast = 'null';
        $userStart = '1970-01-01';
    }
    mysqli_close($sqlConnection);
    $date = new DateTime();
    $date->setTimestamp(strtotime($date0));// + 86400);
    $year = intval($date->format("Y"));
    // I need to figure out this shit.
    $week = intval($date->format("W")) + 1;
    $runningTotal = 0;
    $timestampsCount = count($timestamps);
    // We need the maximum number of stamps in a 'day', use this below to fill in rows that don't have as many
    $maxStamps = 0;
    foreach ($timestamps as $t) {
        $countT = count($t);
        if ($countT > $maxStamps) {
            $maxStamps = $countT;
        }
    }
    foreach ($timestamps as &$timestamp) {
        $day = $timestamp['date'];
        $dayFormatted = date('D m/d', strtotime($day));
        // Add the date column to $echoMe
        // todo: redo parts of code to use the day cell's id to remove 'stamp-day'
        $timestampCount = count($timestamp);
        $timeIn = [];
        $timeOut = [];
        foreach ($timestamp as $key => $stamp) {
            $miss = '';
            // todo: figure out how this works! Past me was obviously smarter
            if (($timestampCount % 2 === 0 && $key === $timestampCount - 2)) {
                $miss = 'missingTime';
            }
            $modifier = $stamp[2];
            // ['date'] will count as a $stamp, so make sure we are dealing with an array
            if (is_array($stamp)) {
                $realTime = date($timeFormat12, $stamp[1]);
                $tri = '';
                if (date($dateFormat, $stamp[1]) !== $day) {
                    $tri = 'overnight';
                }
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
