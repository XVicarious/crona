<?php
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    if (!isset($_POST['userId'])) {
        echo "no user id set";
        return false;
    }
    $userId = $_POST['userId'];
    if (isset($_POST['in'])) {
        $data = $_POST['in'];
        $place = 'schedule_in';
    } elseif (isset($_POST['out'])) {
        $data = $_POST['out'];
        $place = 'schedule_out';
    } else {
        // nothing was set, how do I go on!?
        // Answer: I don't
        return false;
    }
    echo $data;
    echo '<br/>'.$place;
    $year = date('Y', $data);
    $week = date('W', $data);
    $day = date('N', $data);
    echo "<br/>$day";
    // This is AMERICA DAMMIT, Sunday begins the week here.
    if (++$day === 8) {
        $day = 1;
        $week += 1;
    }
    $query = "INSERT INTO employee_schedule (employee_id, $place, schedule_year, schedule_week, schedule_day)
              VALUES ($userId, $data, $year, $week, $day)";
    mysqli_query($sqlConnection, $query);
}
