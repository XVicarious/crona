<?php
date_default_timezone_set('America/New_York');
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $dateFormat = 'Y-m-d H:i:s';
    $employeeId = $_POST['employee'];
    $range = $_POST['range'];
    // Default range is 'this', therefore if something other than the following, it defaults to the current pay period
    $date0 = date($dateFormat, strtotime('last sunday'));
    $date1 = date($dateFormat, strtotime('next saturday 23:59:59'));
    if ($range === 'last') {
        $date0 = date($dateFormat, strtotime('last sunday -1 weeks'));
        $date1 = date($dateFormat, strtotime('last sunday -1 days 23:59:59'));
    } elseif ($range === 'next') {
        $date0 = date($dateFormat, strtotime('next sunday'));
        $date1 = date($dateFormat, strtotime("next saturday 23:59:59 +1 weeks"));
    } elseif ($range === 'specificDate') {
        $date0 = date($dateFormat, $_POST['date0']);
        $date1 = date($dateFormat, $_POST['date1']);
    } elseif ($range === 'w2d') {
        $date0 = date($dateFormat, strtotime('monday 00:00:00'));
        $date1 = date($dateFormat, strtotime('today 23:59:59'));
    }
    $rangeQuery = "AND timestamp_list.datetime BETWEEN '$date0' AND '$date1'";
    $query = "SELECT timestamp_list.stamp_id,timestamp_list.datetime,timestamp_list.stamp_special,
              timestamp_list.stamp_department FROM timestamp_list WHERE timestamp_list.user_id_stamp = $employeeId " .
              $rangeQuery . ' ORDER BY timestamp_list.datetime';
    $queryResult = mysqli_query($sqlConnection, $query);
    if ($queryResult === false) {
        return;
    }
    $timestamps = [];
    while (list($stampId, $timestamp, $modifier, $depart) = mysqli_fetch_row($queryResult)) {
        $lastTimestamp = end($timestamps);
        $dateEDT = new DateTimeZone('America/New_York');
        $offsetSeconds = $dateEDT->getOffset(new DateTime("now"));
        $thisDay = date('Y-m-d', strtotime($timestamp) + $offsetSeconds);
        $thisTime = date('h:i:s a', strtotime($timestamp) + $offsetSeconds);
        if ($lastTimestamp['date'] === $thisDay && count($lastTimestamp) - 1 < 6) {
            $timestamps[count($timestamps) - 1]['date'] = $thisDay;
            array_push($timestamps[count($timestamps) - 1], [$stampId, $thisTime, $modifier, $depart]);
        } else {
            array_push($timestamps, ['date' => $thisDay, [$stampId, $thisTime, $modifier, $depart]]);
        }
    }

    $quickQuery = "SELECT user_first,user_last,user_start FROM employee_list WHERE user_id = $employeeId";
    $quickResult = mysqli_query($sqlConnection, $quickQuery);
    if (mysqli_num_rows($quickResult) !== 0) {
        list($userfirst, $userlast, $userstart) = mysqli_fetch_row($quickResult);
    } else {
        $userfirst = "null";
        $userlast = "null";
        $userstart = "1970-01-01";
    }

    $addAfter = date($dateFormat, strtotime($timestamps[count($timestamps) - 1]['date'] . ' 00:00:00 +1 days'));
    $echoMe = "<input type=\"date\" id=\"date0\" class=\"datepicker\"><input type=\"date\" id=\"date1\"
                class=\"datepicker\"><table id=\"timecard\" user-start=\"$userstart\" user-id=\"$employeeId\">
               <tr id=\"topTR\"><th id=\"topTH\" colspan=\"100%\">$userfirst $userlast's Timecard<select id=\"range\"
                class=\"browser-default\"><option value=\"last\">Previous Period</option><option value=\"this\"
                selected>Current Period</option><option value=\"next\">Next Period</option><option class=\"sp\"
                value=\"specificDate\">Specific Date</option><option value=\"w2d\">Week to Date</option><option
                value=\"special\">Specific Period</option></select></th></tr><tr id=\"headings\"><th></th><th>Date</th>
               <th colspan=\"100%\"></th></tr>";
    $rowNumber = 0;
    $runningTotal = 0;
    foreach ($timestamps as $timestamp) {
        $rowNumber++;
        $day = $timestamp['date'];
        $addBefore = date($dateFormat, strtotime($day . ' 00:00:00 -1 days'));
        $isOdd = ($rowNumber % 2 ? "" : 'odd');
        $modDay = date('D m/d', strtotime($day));
        $echoMe .= "<tr stamp-day=\"$day\" class=\"dataRow $isOdd\"><td class=\"newDate\"><input class=\"newDate\"
                     type=\"button\" value=\"<+\"></td><td class=\"dayCell\" id=\"$day\">$modDay</td>";
        $timeIns = [];
        $timeOuts = [];
        $matchingSpecial = true;
        $chosenModifier = '';
        foreach ($timestamp as $t) {
            if ($t[2] != $chosenModifier) {
                $matchingSpecial = false;
                $chosenModifier = $t[2];
            } else {
                $matchingSpecial = true;
            }
        }

        $tyme = 0;
        $c_timestamp = count($timestamp);
        for ($i = 0; $i < $c_timestamp - 1; ++$i) {
            $t_sid = $timestamp[$i][0];
            $t_tms = $timestamp[$i][1];
            $t_mod = $timestamp[$i][2];
            $t_dep = $timestamp[$i][3];
            $miss = "";
            if (($c_timestamp % 2 === 0 && $i === $c_timestamp - 2) || !$matchingSpecial) {
                $miss = "missingTime";
            }

            $echoMe .= "<td class=\"tstable addButton\"><input class=\"addButton\" type=\"button\" value=\"+\"></td>
                        <td department-special=\"$t_dep\" class=\"tstable $t_mod $miss times\">";
            if ($t_mod === 'S') {
                $val = 'SICK';
                $disabled = 'readonly disabled';
            } elseif ($t_mod === 'F') {
                $val = 'BEREAVEMENT';
                $disabled = 'readonly disabled';
            } elseif ($t_mod === 'V') {
                $val = 'VACATION';
                $disabled = 'readonly disabled';
            } else {
                $val = $t_tms;
                $disabled = '';
            }

            $echoMe .= "<input $disabled title=\"$t_mod\" alt=\"$t_mod\" class=\"times context-menu\"
                         default-time=\"$t_tms\" stamp-id=\"$t_sid\" type=\"text\"  maxlength=\"11\" id=\"$t_sid\"
                         value=\"" . $val . "\"></td>";
            if ($i % 2) {
                array_push($timeOuts, strtotime($t_tms));
            } else {
                array_push($timeIns, strtotime($t_tms));
            }

            $tyme = $i;
        }

        $echoMe .= '<td class="addButton after"><input class="addButton" type="button" value="+"></td>';
        $timeTotal = 0;
        for ($i = 0; $i < count($timeOuts); ++$i) {
            $timeTotal += ($timeOuts[$i] - $timeIns[$i]);
        }

        for ($i = 0; $i < 5 - $tyme; ++$i) {
            $echoMe .= '<td colspan=2 class="overflow"></td>';
        }

        $timeTotal *= (($chosenModifier === 'H' && (strtotime($day . ' 00:00:00') - strtotime($userstart . ' 00:00:00'))
                       >= 7776000) ? 2 : 1);
        $timeTotal = round($timeTotal / 3600, 2);
        $echoMe .= '<td class="dailyHours" colspan"99%">';
        $echoMe .= number_format($timeTotal, 2);
        $echoMe .= '</td></tr>';
        $runningTotal += $timeTotal;
    }
    $ot = '';
    if ($runningTotal > 40) {
        $otHours = $runningTotal - 40;
        $otHours = number_format($otHours, 2);
        $ot = "title=\"Normal Hours: 40&#xA;Overtime Hours: $otHours\"";
    }
    $echoMe .= '<tr class="dataRow"><td class="newDate after" colspan="100%"><input class="newDate after" type="button"
                 value="+"></td></td><tr class="dataRow"><td colspan="15"></td><td ' . $ot . ' class="dailyHours">';
    $echoMe .= number_format($runningTotal, 2);
    $echoMe .= '</td></tr><tr><th colspan="100%">Timecard</th></tr></table>
                <p style="margin:0;font-size:50%;">written by: Brian Maurer</p>';
    mysqli_close($sqlConnection);
    echo $echoMe;
}
