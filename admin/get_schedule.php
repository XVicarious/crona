<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    // Use this to select the starting page
    $employee = $_POST['employee'];
    $year = $_POST['year'];
    $week = $_POST['week'];
    $scheduleResult = mysqli_query($sqlConnection, "SELECT schedule_day,schedule_in,schedule_out,schedule_department FROM employee_schedule WHERE employee_id = $employee AND schedule_week = $week AND schedule_year = $year");
    $a_masterSchedule = [];
    if (mysqli_num_rows($scheduleResult) !== 0) {
        while(list($scheduleDay,$scheduleIn,$scheduleOut,$scheduleDepartment) = mysqli_fetch_row($scheduleResult)) {
            array_push($a_masterSchedule,["day"=>$scheduleDay,"in"=>$scheduleIn,"out"=>$scheduleOut,"department"=>$scheduleDepartment]);
        }
    }
    echo json_encode($a_masterSchedule,JSON_NUMERIC_CHECK);
    mysqli_close($sqlConnection);
}