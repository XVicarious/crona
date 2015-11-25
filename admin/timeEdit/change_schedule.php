<?php
// todo: update SQL
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $id = $_POST['id'];
    $in = $_POST['in'];
    $out = $_POST['out'];
    $department = $_POST['department'];
    $query = 'UPDATE employee_schedule SET ';
    if (isset($in)) {
        $in = intval($in);
        $query .= "schedule_in = $in";
        if (isset($out) || isset($department)) {
            $query .= ', ';
        }
    }
    if (isset($out)) {
        $out = intval($out);
        $query .= "schedule_out = $out";
        if (isset($department)) {
            $query .= ', ';
        }
    }
    if (isset($department)) {
        $department = intval($department);
        $query .= "schedule_department = $department";
    }
    $id = intval($id);
    $query .= " WHERE schedule_id = $id";
    mysqli_query($sqlConnection, $query);
}
