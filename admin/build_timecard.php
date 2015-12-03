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
    $json = $_POST['timestamps'];
    $mode = 2;
    $timestamps = json_decode($json, true);
    $firstDate = $timestamps[0]['date'];
    $modifiable = true;
    if (count($timestamps) > 8) {
        pre($timestamps);
        $modifiable = false;
    }
    $sundayYear = 0;
    $sundayWeek = 0;
    if ($firstDate) {
        $day = date('w', strtotime($firstDate));
        $sundayYear = date('Y', strtotime($firstDate.' -'.$day.' days'));
        $sundayWeek = date('W', strtotime($firstDate.' -'.$day.' days')) + 1;
    } else {
        // todo: figure out what I should do here!
        die();
    }
    $dbh = createPDO();
    $isLocked = 0;
    try {
        $stmt = $dbh->prepare(SqlStatements::GET_IS_LOCKED, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':userid', $timestamps['USER_INFO']['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':pyear', $sundayYear, PDO::PARAM_INT);
        $stmt->bindParam(':pweek', $sundayWeek, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $isLocked = $result[0];
        $key = key($isLocked);
        $isLocked = $isLocked[$key];
    } catch (Exception $e) {
        die();
    }
    $dbh = null;
    $countStamps = count($timestamps);
    $timestampTable = '<input type="date" id="date0" class="datepicker">
                       <input type="date" id="date1" class="datepicker">
                       <table id="timecard" user-id="'.$timestamps['USER_INFO']['user_id'].'" year="'.$sundayYear.'" week="'.$sundayWeek.'">
                        <tr id="topTR">
                         <th id="topTH" colspan="100%">
                          '.$timestamps['USER_INFO']['user_first'].' '.$timestamps['USER_INFO']['user_last'].'\'s Timecard
                          <select id="range" class="browser-default">
                           <option value="last">Previous Period</option>
                           <option value="this">Current Period</option>
                           <option value="next">Next Period</option>
                           <option value="specificDate">Specific Date</option>
                           <option value="special">Specific Period</option>
                          </select>
                         </th>
                        </tr>
                        <tr>
                         <th>Date</th>
                         <th colspan="100%"></th>
                        </tr>';
    $totalSeconds = 0;
    $maxStamps = 0;
    for ($i = 0; $i < $countStamps - 1; ++$i) {
        $countT = count($timestamps[$i]) - 2;
        if ($countT > $maxStamps) {
            $maxStamps = $countT;
        }
    }
    for ($i = 0; $i < $countStamps - 1; ++$i) {
        $tempStamp = $timestamps[$i];
        $day = $tempStamp['date'];
        $dayFormatted = date('D m/d', strtotime($day));
        $timestampTable .= "<tr stamp-day=\"$day\" class=\"dataRow\">";
        $timestampTable .= "<td class=\"dayCell\" id=\"$day\">$dayFormatted</td>";
        $timestampCount = count($tempStamp) - 2;
        if ($timestampCount > 0) {
            foreach ($tempStamp as $key => $stamp) {
                // ['date'] counts as a stamp according to stuff, so we need to make sure we select an array!
                if (is_array($stamp)) {
                    $miss = '';
                    // if the number of timestamps is ODD, and this stamp is the last in the array,
                    // there is a time missing
                    if ($timestampCount % 2 === 1 && $key === $timestampCount - 1) {
                        $miss = 'missingTime';
                    }
                    $modifier = $stamp[2];
                    if ($mode === 2 && !$isLocked && $modifiable) {
                        $timestampTable .= "<td class=\"tstable addButton ts-card\">
                                             <button class=\"addButton\" type=\"button\">
                                              <i class=\"material-icons\">add</i>
                                             </button>
                                            </td>";
                    }
                    $realTime = date($timeFormat12, $stamp[1]);
                    $tri = ''; //tri stood for something... I forget what though
                    if (date($dateFormat, $stamp[1]) !== $day) {
                        $tri = 'overnight';
                    }
                    $val = $realTime;
                    $disabled = '';
                    if ($modifier !== '') {
                        $disabled = 'readonly';
                        switch ($modifier) {
                            case 'S':
                                $val = 'SICK';
                                break;
                            case 'F':
                                $val = 'BEREAVEMENT';
                                break;
                            case 'V':
                                $val = 'VACATION';
                                break;
                            default:
                                break;
                        }
                    } elseif ($mode === 0 || $mode === 1 || $isLocked || !$modifiable) {
                        $disabled = 'disabled readonly';
                    }
                    $timestampTable .= "<td class=\"droppableTimes times ts-card tstable $tri $miss\">
                                     <div class=\"draggableTimes\">
                                      <input class=\"times ts-card context-menu\"
                                             stamp-id=\"$stamp[0]\"
                                             id=\"$stamp[0]\"
                                             default-time=\"$realTime\"
                                             type=\"text\"
                                             value=\"$val\"
                                             title=\"$stamp[5]\"
                                             $disabled>
                                     </div>";
                }
            }
        }
        if ($mode === 2 && !$isLocked && $modifiable) {
            $colspan = ($maxStamps - $timestampCount + 0.5) * 2;
            $timestampTable .= "<td class=\"addButton after ts-card\" colspan=\"$colspan\">
                                 <button class=\"addButton ts-card\" type=\"button\">
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
