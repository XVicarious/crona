<?php
$startTime = microtime(true);
date_default_timezone_set('UTC'); // todo: make timezone configurable
require 'admin_functions.php';
include 'SqlStatements.php';
$dateFormat = 'Y-m-d';
$timeFormat24 = 'H:i:s';
$timeFormat12 = 'h:i:s a';
$dateTimeFormat24 = $dateFormat.' '.$timeFormat24;
if (sessionCheck()) {
    $json = $_POST['timestamps'];
    $mode = 2;
    $timestamps = json_decode($json, true);
    //header('X-PJAX-URL: https://xvss.net/devel/time/admin/schedule/'.$timestamps[0]['userid']);
    $modifiable = true;
    $countStamps = count($timestamps);
    $timestampTable = '<input type="date" id="date0" class="datepicker">
                       <input type="date" id="date1" class="datepicker">
                       <table id="timecard" user-id="'.$timestamps[0]['userid'].'">
                        <tr id="topTR">
                         <th id="topTH" colspan="100%">
                          '.$timestamps['USER_INFO']['user_first'].' '.$timestamps['USER_INFO']['user_last'].'\'s Timecard
                          <!--<select id="range" class="browser-default">
                           <option value="last">Previous Period</option>
                           <option value="this">Current Period</option>
                           <option value="next">Next Period</option>
                           <option value="w2d">Week to Date</option>
                           <option value="specificDate">Specific Date</option>
                           <option value="special">Specific Period</option>
                          </select>-->
                         </th>
                        </tr>
                        <tr>
                         <th>Date</th>
                         <th colspan="100%"></th>
                        </tr>';
    $totalSeconds = 0;
    $maxStamps = 0;
    for ($i = 0; $i < $countStamps; ++$i) {
        $countT = count($timestamps[$i]) - 2;
        if ($countT > $maxStamps) {
            $maxStamps = $countT;
        }
    }
    $day = new DateTime();
    for ($i = 1; $i < $countStamps; ++$i) {
        $day->setTimestamp($timestamps[$i][0]['schedule_unix']);
        $date = $day->format($dateFormat);
        $displayDate = $day->format('D m/d');
        $timestampTable .= "<tr stamp-day=\"$date\" class=\"dataRow\">";
        $timestampTable .= "<td class=\"dayCell\" id=\"\">$displayDate</td>";
        $timestampCount = count($timestamps[$i]) - 2;
        foreach ($timestamps as $key => $tempStamp) {
            $day->setTimestamp($tempStamp['schedule_unix']);
            $stampTime = $day->format($timeFormat12);
            if (is_array($tempStamp)) {
                $timestampTable .= "<td class=\"droppableTimes times tstable\">
                                     <div class=\"draggableTimes\">
                                      <input class=\"times context-menu sched-in sched\"
                                             stamp-id=\"".$tempStamp['schedule_id']."\"
                                             id=\"\"
                                             default-time=\"\"
                                             type=\"text\"
                                             value=\"$stampTime\"
                                             title=\"\">
                                     </div></td>";
            }
        }
        /*if ($timestampCount > 0) {
            if (is_array($tempStamp)) {
                if ($mode === 2 && !$isLocked && $modifiable) {
                    $timestampTable .= "<td class=\"tstable addButton\">
                                             <button class=\"addButton\" type=\"button\">
                                              <i class=\"material-icons\">add</i>
                                             </button>
                                            </td>";
                }
                $timeZone = new DateTimeZone('America/New_York');
                $stampTime = $day->format($timeFormat12);

            }
        }*/
        if ($mode === 2 && !$isLocked && $modifiable) {
            $colspan = ($maxStamps - $timestampCount + 0.5) * 2;
            $timestampTable .= "<td class=\"addButton after\" colspan=\"$colspan\">
                                 <button class=\"addButton\" type=\"button\">
                                  <i class=\"material-icons\">add</i>
                                 </button>
                                </td>";
        } else {
            $colspan = ($maxStamps - $timestampCount);
            if ($colspan !== 0) {
                $timestampTable .= "<td colspan=\"$colspan\"></td>";
            }
        }
        $colCount = $maxStamps - $timestampCount;
        $colspan = 0;
        for ($j = 0; $j < $colCount; ++$j) {
            if ($mode === 2) {
                $colspan = $colCount * 2;
            }
        }
        $timestampTable .= '';
        $timeTotal = round($tempStamp['totalTime'] / 3600, 2);
        $timestampTable .= '<td class="dailyHours">'.number_format($timeTotal, 2).'</td></tr>';
        $totalSeconds += $timeTotal;
    }
    $push = 1 + $maxStamps;
    if ($mode === 2 && !$isLocked && $modifiable) {
        $push = 2 + (2 * $maxStamps);
    }
    $timestampTable .= "<tr class=\"dataRow\">
                         <td colspan=\"$push\"></td>
                         <td class=\"dailyHours\">".number_format($totalSeconds, 2).'</td>
                        </tr>
                        <tr>
                         <th colspan="100%">Timecard';
    if ($mode !== 1) {
        $timestampTable .= '<a class="btn-flat wave-effect right"
                               href="utility/export_timecard.html&userid='.$timestamps['USER_INFO']['user_id'].'"
                               target="_blank">
                             <i class="material-icons">print</i>
                            </a>';
    }
    if ($mode === 2 && !$isLocked && $modifiable) {
        $timestampTable .= '<a id="lock-card" href="#" class="btn-flat right"
                               title="Prevent further editing of the timecard for this period">
                             <i class="material-icons">lock</i>
                            </a>';
    }
    $timestampTable .= '</th></tr></table>';
    echo $timestampTable;
}
$endTime = microtime(true);
$totalTime = $endTime - $startTime;
$memory = memory_get_usage(true);
if ($totalTime < 0 || $totalTime >= 1) {
    error_log(__FILE__.' executed in '.$totalTime.' by userId:'.$_SESSION['userId'].' using '.$memory.'b', 0);
}
