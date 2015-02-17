<?php
session_start();
require 'admin_functions.php';
$sqlConnection = createSql();
$administrativeId = $_SESSION['userId'];
if (isset($administrativeId)) {
    $adminResults = mysqli_query($sqlConnection, "SELECT user_admin_perms FROM employee_list WHERE user_id = $administrativeId");
    if (mysqli_num_rows($adminResults) !== 0) {
        $a_codes = [];
        list($sa_adminperms) = mysqli_fetch_row($adminResults);
        if ($sa_adminperms !== "all") {
            $a_adminperms = unserialize($sa_adminperms);
            $a_codes = array_keys($a_adminperms);
        }
        $exportCompany = $_POST['companyCode'];
        echo $exportCompany;
        // To prevent spoofing, make sure the person has permissions, if not end it
        if ($sa_adminperms !== "all" && !in_array($exportCompany, $a_codes)) {
            return;
        }
        $peopleResults = mysqli_query($sqlConnection, "SELECT user_id,user_adpid FROM employee_list WHERE user_companycode = '$exportCompany'");
        $dateFormat = 'Y-m-d H:i:s';
        $date0 = date($dateFormat, strtotime('last sunday -1 weeks'));
        $date1 = date($dateFormat, strtotime('last sunday -1 days 23:59:59'));
        $whereDate = "AND datetime BETWEEN '$date0' AND '$date1'";
        if (mysqli_num_rows($peopleResults) !== 0) {
            $a_people = [];
            while (list($userId,$adpId) = mysqli_fetch_row($peopleResults)) {
                array_push($a_people, [$userId,$adpId]);
            }
            // Now that we have a list of people, we need their timestamps
            $whereUserId = 'WHERE (';
            $ca_people = count($a_people);
            for ($i = 0; $i < $ca_people; $i++) {
                $whereUserId .= 'user_id_stamp = '.$a_people[$i][0];
                if ($i < $ca_people-1) {
                    $whereUserId .= ' OR ';
                }
            }
            // Retrieve the timestamps from the database
            $whereUserId .= ") AND datetime BETWEEN '$date0' AND '$date1'";
            $query = "SELECT user_id_stamp,datetime,stamp_special,stamp_department FROM timestamp_list $whereUserId ORDER BY datetime";
            $timestampResults = mysqli_query($sqlConnection, $query);
            if (mysqli_num_rows($timestampResults) !== 0) {
                $a_timestamps = [];
                while (list($userId,$datetime,$modifier,$changeDepartment) = mysqli_fetch_row($timestampResults)) {
                    // Push the timestamps into an array
                    // Index of the stamp will be the user's id number from the database
                    if ($a_timestamps[$userId] === null) {
                        $a_timestamps[$userId] = [];
                    }
                    // push an array of timestamps
                    array_push($a_timestamps[$userId], [$datetime,$modifier,$changeDepartment]);
                }
                $a_userHours = [];
                // Headers for the CSV file
                array_push($a_userHours, ['Co Code','Batch ID','File #','Reg. Hours','O/T Hours','Hours 4 Code','Hours 4 Amount','Hours 4 Code','Hours 4 Amount','Hours 4 Code','Hours 4 Amount','Hours 4 Code','Hours 4 Amount','Hours 4 Code','Hours 4 Amount','Earnings 3 Code','Earnings 3 Amount','Earnings 3 Code','Earnings 3 Amount']);
                foreach ($a_people as $user) {
                    $userKey = $user[0];
                    $totalHours = 0;
                    $totalHolidayHours = 0;
                    $totalSickHours = 0;
                    $totalPersonalHours = 0;
                    $totalSadHours = 0;
                    $totalVacationHours = 0;
                    $overtimeHours = 0;
                    for ($i = 0; $i <= count($a_timestamps[$userKey])-1; $i+=2) {
                        $t_timestamp = $a_timestamps[$userKey][$i];
                        $t_timestamp_2 = $a_timestamps[$userKey][$i+1];
                        $t_hours = (strtotime($t_timestamp_2[0]) - strtotime($t_timestamp[0]))/3600;
                        if ($t_timestamp[1] === "" || $t_timestamp[1] === "H") {
                            $totalHours += $t_hours;
                            if ($t_timestamp[1] === "H") {
                                $totalHolidayHours += $t_hours;
                            }
                        } elseif ($t_timestamp[1] === "S" && $t_timestamp_2[1] === "S") {
                            $totalSickHours += $t_hours;
                        } elseif ($t_timestamp[1] === "V" && $t_timestamp_2[1] === "V") {
                            $totalVacationHours += $t_hours;
                        } elseif ($t_timestamp[1] === "P" && $t_timestamp_2[1] === "P") {
                            $totalPersonalHours += $t_hours;
                        } elseif ($t_timestamp[1] === "F" && $t_timestamp_2[1] === "F") {
                            $totalSadHours += $t_hours;
                        }
                    }
                    $totalHours = round($totalHours, 2);
                    if ($totalHours > 40) {
                        $overtimeHours = $totalHours - 40;
                        $totalHours = 40;
                    }
                    array_push($a_userHours, [$exportCompany,50,$user[1],$totalHours,$overtimeHours,'S',round($totalSickHours, 2),'V',round($totalVacationHours, 2),'P',round($totalPersonalHours, 2),'H',round($totalHolidayHours / 3600, 2),'F',round($totalSadHours, 2),'B',0,'T',0]);
                }
                $fileName = "tmp/$exportCompany-".time().'.csv';
                $csv = fopen($fileName, 'w');
                foreach ($a_userHours as $fields) {
                    fputcsv($csv, $fields);
                }
                fclose($csv);
                echo "<script>window.open(\"http://xvss.net/time/admin/$fileName\")</script>";
            }
        }
    }
}

/*
 * CSVFormatting
 *
 * Co Code (CompanyCode)
 * Batch ID (always 50)
 * File # (ADP ID)
 * Reg. Hours (hours not special, add normal holiday hours)
 * O/T Hours (any hours over 40)
 * Hours 4 Code (S)
 * Hours 4 Amount (amount of sick hours)
 * Hours 4 Code (V)
 * Hours 4 Amount (amount of vacation hours)
 * Hours 4 Code (P)
 * Hours 4 Amount (amount of personal hours)
 * Hours 4 Code (H)
 * Hours 4 Amount (amount of holiday hours)
 * Hours 4 Code (F)
 * Hours 4 Amount (amount of sad hours)
 * Earnings 3 Code (B)
 * Earnings 3 Amount (banquet tips)
 * Earnings 3 Code (T)
 * Earnings 3 Amount (normal tips)
 *
 */
