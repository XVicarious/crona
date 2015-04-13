<?php
date_default_timezone_set('America/New_York'); // todo: make timezone configurable
require 'admin_functions.php';
$sqlConnection = createSql();
$dateFormat = 'Y-m-d';
$timeFormat24 = 'H:i:s';
$timeFormat12 = 'h:i:s a';
$dateTimeFormat24 = $dateFormat.' '.$timeFormat24;
if (sessionCheck()) {
    $employeeId = $_POST['employee'];
    $range = $_POST['range'];
    // todo: for the dates, find a better way for reference.  'last sunday' won't work correctly if today is sunday
    // todo: previous todo also applies to saturday, though this issue isn't as severe
    $date0 = date($dateTimeFormat24, strtotime('last sunday'));
    $date1 = date($dateTimeFormat24, strtotime('next saturday 23:59:59'));
    if ($range === 'last') {
        $date0 = date($dateTimeFormat24, strtotime('last sunday -1 weeks'));
        $date1 = date($dateTimeFormat24, strtotime('last sunday -1 days 23:59:59'));
    } elseif ($range === 'next') {
        $date0 = date($dateTimeFormat24, strtotime('next sunday'));
        $date1 = date($dateTimeFormat24, strtotime("next saturday 23:59:59 +1 weeks"));
    } elseif ($range === 'specificDate') {
        $date0 = date($dateTimeFormat24, $_POST['date0']);
        $date1 = date($dateTimeFormat24, $_POST['date1']);
    } elseif ($range === 'w2d') {
        $date0 = date($dateTimeFormat24, strtotime('monday 00:00:00'));
        $date1 = date($dateTimeFormat24, strtotime('today 23:59:59'));
    }
    $query = "SELECT stamp_id,timestamp_list.datetime,stamp_special,stamp_department,stamp_partner
              FROM timestamp_list
              WHERE user_id_stamp = $employeeId
              AND timestamp_list.datetime BETWEEN '$date0' AND '$date1'
              ORDER BY timestamp_list.datetime";
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
            //$timestamps[$timestampsCount]['date'] = $thisDay;
            array_push($timestamps[$timestampsCount], [$stampId, $thisUnix, $modifier, $department, $partnerId]);
        } else {
            // if this is a new day (or it doesn't reference the previous day) then start a new day
            array_push($timestamps, ['date'=>$thisDay, [$stampId, $thisUnix, $modifier, $department, $partnerId]]);
        }
        //  We need the stampIds to tell if the stamps reference one another
        array_push($stamps, $stampId);
    }
    // todo: breaks could be an issue here!
    // fixme: If there are no stamps, array_splice errors our!
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
    // Make sure this is here before anything
    $echoMe = '';
    // Add the datepicker stuff.
    $echoMe .= '<input type="date" id="date0" class="datepicker"><input type="date" id="date1" class="datepicker">';
    // Add the table's opening tag
    $echoMe .= "<table id=\"timecard\" user-id=\"$employeeId\">"; // todo: is there a better way to store the id?
    // Add the header, sans range selector
    $echoMe .= "<tr id=\"topTR\"><th id=\"topTH\" colspan=\"100%\">$userFirst $userLast's Timecard";
    // Add the select, close the header
    $echoMe .= '<select id="range" class="browser-default">
                 <option value="last">Previous Period</option>
                 <option value="this">Current Period</option>
                 <option value="next">Next Period</option>
                 <option value="w2d">Week to Date</option>
                 <option value="specificDate">Specific Date</option>
                 <option value="special">Specific Period</option>
                </select></th></tr>';
    // Finally for the head, the (shitty) header for date and junk
    $echoMe .= '<tr><th>Date</th><th colspan="100%"></th></tr>';
    /*
     * Total addition for hours
     */
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
    foreach ($timestamps as $timestamp) {
        $day = $timestamp['date'];
        $dayFormatted = date('D m/d', strtotime($day));
        // Add the date column to $echoMe
        // todo: redo parts of code to use the day cell's id to remove 'stamp-day'
        $echoMe .= "<tr stamp-day=\"$day\" class=\"dataRow\">";
        $echoMe .= '<td class="newDate"><input class="newDate" type="button" value="<+"</td>';
        $echoMe .= "<td class=\"dayCell\" id=\"$day\">$dayFormatted</td>";
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
                $echoMe .= '<td class="tstable addButton"><input class="addButton" type="button" value="+"></td>';
                $realTime = date($timeFormat12, $stamp[1]);
                $tri = "";
                if (date($dateFormat, $stamp[1]) !== $day) {
                    $tri = "overnight";
                }
                $val = $realTime;
                $disabled = '';
                if ($modifier !== '') {
                    $disabled = 'readonly disabled';
                    if ($modifier === 'S') {
                        $val = 'SICK';
                    } elseif ($modifier === 'F') {
                        $val = 'BEREAVEMENT';
                    } elseif ($modifier === 'V') {
                        $val = 'VACATION';
                    }
                }
                $echoMe .= "<td class=\"times tstable $tri $miss\">
                             <input $disabled class=\"times context-menu\" stamp-id=\"$stamp[0]\" id=\"$stamp[0]\" default-time=\"$realTime\" type=\"text\" value=\"$val\">
                            </td>";
                if ($key % 2) {
                    array_push($timeOut, $stamp[1]);
                } else {
                    array_push($timeIn, $stamp[1]);
                }
            }
        }
        $echoMe .= '<td class="addButton after"><input class="addButton" type="button" value="+"></td>';
        $timeTotal = 0;
        for ($i = 0; $i < count($timeOut); ++$i) {
            $timeTotal += ($timeOut[$i] - $timeIn[$i]);
        }
        for ($i = 0; $i < $maxStamps - $timestampCount; ++$i) {
            // Fill in rows that don't have the maximum number of stamps.
            $echoMe .= '<td colspan=2 class="overflow"></td>';
        }
        $timeTotal = round($timeTotal / 3600, 2);
        $echoMe .= '<td class="dailyHours">';
        $echoMe .= number_format($timeTotal, 2);
        $echoMe .= '</td>';
        $echoMe .= '</tr>';
        $runningTotal += $timeTotal;
    }
    $echoMe .= '<tr class="dataRow"><td class="newDate after" colspan="100%"><input class="newDate after" type="button" value="+"</td></tr>';
    $push = $maxStamps * 2 + 1;
    $echoMe .= "<tr class=\"dataRow\"><td colspan=\"$push\"></td><td class=\"dailyHours\">";
    $echoMe .= number_format($runningTotal, 2);
    $echoMe .= '</td></tr><tr><th colspan="100%">Timecard</th></tr>';
    $echoMe .= '</table>';
    mysqli_close($sqlConnection);
    echo $echoMe;
}
