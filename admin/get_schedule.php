<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    // Use this to select the starting page
    $employee = $_GET['userId'];
    $year = $_GET['year'];
    $week = $_GET['week'];
    $q = "SELECT schedule_day,schedule_in,schedule_out,schedule_department FROM employee_schedule
          WHERE employee_id = $employee AND schedule_week = $week AND schedule_year = $year";
    $scheduleResult = mysqli_query($sqlConnection, $q);
    $a_masterSchedule = [];
    if (mysqli_num_rows($scheduleResult) !== 0) {
        while (list($scheduleDay,$scheduleIn,$scheduleOut,$scheduleDepartment) = mysqli_fetch_row($scheduleResult)) {
            array_push($a_masterSchedule, ["day" => $scheduleDay,
                                           "in" => $scheduleIn,
                                           "out" => $scheduleOut,
                                           "department" => $scheduleDepartment]);
        }
    }
    // If the schedule doesn't contain every day of the week, add them
    $scheduledDays = [];
    foreach ($a_masterSchedule as $day) {
        array_push($scheduledDays, $day['day']);
    }
    for ($i = 1; $i <= 7; $i++) {
        if (!in_array($i, $scheduledDays)) {
            // Only send the day and default department
            array_push($a_masterSchedule, ['day' => $i,
                                           'department' => 0]);
        }
    }
    echo json_encode($a_masterSchedule, JSON_NUMERIC_CHECK);
    mysqli_close($sqlConnection);
}
