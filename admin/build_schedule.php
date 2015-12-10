<?php
$startTime = microtime(false);
date_default_timezone_set('America/New_York'); // todo: make timezone configurable
require 'admin_functions.php';
include 'SqlStatements.php';
$dateFormat = 'Y-m-d';
$timeFormat24 = 'H:i:s';
$timeFormat12 = 'h:i:s a';
$dateTimeFormat24 = $dateFormat.' '.$timeFormat24;
const SCHD_ID = 'scheudle_id';
const TIME_IN = 'schedule_in';
const TIME_OUT = 'schedule_out';
if (sessionCheck()) {
    $json = $_POST['timestamps'];
    $mode = 2;
    $timestamps = json_decode($json, true);
    pre($timestamps);
    $modifiable = true;
    $sundayYear = $timestamps[0]['year'];
    $sundayWeek = $timestamps[0]['week'];
    $countStamps = count($timestamps);
    $timestampTable = '<input type="date" id="date0" class="datepicker">
                       <input type="date" id="date1" class="datepicker">
                       <table id="timecard" user-id="'.$timestamps[0]['userid'].'" year="'.$sundayYear.'" week="'.$sundayWeek.'">
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
    for ($i = 1; $i < $countStamps; ++$i) {
        $tempStamp = $timestamps[$i];
        $day = new DateTime();
        $day->setISODate($sundayYear, $sundayWeek, $tempStamp['schedule_day']);
        $day = $day->format('Y-m-d');
        $dayFormatted = date('D m/d', strtotime($day));
        $timestampTable .= "<tr stamp-day=\"$day\" class=\"dataRow\">";
        $timestampTable .= "<td class=\"dayCell\" id=\"$day\">$dayFormatted</td>";
        $timestampCount = count($tempStamp) - 2;
        if ($timestampCount > 0) {
            //foreach ($tempStamp as $key => $stamp) {
                // ['date'] counts as a stamp according to stuff, so we need to make sure we select an array!
                if (is_array($tempStamp)) {
                    $miss = '';
                    // if the number of timestamps is ODD, and this stamp is the last in the array,
                    // there is a time missing
                    if ($timestampCount % 2 === 1 && $key === $timestampCount - 1) {
                        $miss = 'missingTime';
                    }
                    $modifier = $tempStamp[2];
                    if ($mode === 2 && !$isLocked && $modifiable) {
                        $timestampTable .= "<td class=\"tstable addButton\">
                                             <button class=\"addButton\" type=\"button\">
                                              <i class=\"material-icons\">add</i>
                                             </button>
                                            </td>";
                    }
                    $realTime = date($timeFormat12, $tempStamp[1]);
                    $tri = ''; //tri stood for something... I forget what though
                    if (date($dateFormat, $stamp[1]) !== $day) {
                        $tri = 'overnight';
                    }
                    $val = $realTime;
                    // add the time in
                    $timestampTable .= "<td class=\"droppableTimes times tstable $tri $miss\">
                                     <div class=\"draggableTimes\">
                                      <input class=\"times context-menu\"
                                             stamp-id=\"$tempStamp[TIME_IN]\"
                                             id=\"$tempStamp[SCHD_ID]\"
                                             default-time=\"$realTime\"
                                             type=\"text\"
                                             value=\"$val\"
                                             title=\"$tempStamp[5]\">
                                     </div>";
                    // add the time out
                    $timestampTable .= "<td class=\"droppableTimes times tstable $tri $miss\">
                                     <div class=\"draggableTimes\">
                                      <input class=\"times context-menu\"
                                             stamp-id=\"$tempStamp[TIME_OUT]\"
                                             id=\"$tempStamp[SCHD_ID]\"
                                             default-time=\"$realTime\"
                                             type=\"text\"
                                             value=\"$val\"
                                             title=\"$tempStamp[5]\">
                                     </div>";
                }
            //}
        }
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
$endTime = microtime(false);
$totalTime = $endTime - $startTime;
$memory = memory_get_usage(true);
if ($totalTime < 0 || $totalTime >= 1) {
    error_log(__FILE__.' executed in '.$totalTime.' by userId:'.$_SESSION['userId'].' using '.$memory.'b', 0);
}
