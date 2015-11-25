<?php
// todo: update SQL
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $apt_user = $_POST['user']; // the user's card being locked
    $apt_admin = $_SESSION['userId']; // the admin doing the locking
    $apt_year = $_POST['year']; // the year the timecard falls in (YYYY)
    $apt_week = $_POST['week']; // the week of the year the timecard falls in 01 - 52
    $apt_locked = $_POST['locked'] || false; //  is this the FINAL lock?  If not, this is false
    // is this the best way to do this?  probably not, but I don't want to double dip in the query
    // todo: this is something that should be checked for an admin
    if ($apt_locked) {
        $lock_query = "UPDATE approved_timecards SET apt_locked = TRUE
                       WHERE apt_user = $apt_user AND apt_year = $apt_year AND apt_week = $apt_week";
    } else {
        $lock_query = "INSERT INTO approved_timecards (apt_user,apt_admin,apt_year,apt_week,apt_locked)
                       VALUES ($apt_user,$apt_admin,$apt_year,$apt_week,FALSE)";
    }
    mysqli_query($sqlConnection, $lock_query);
    mysqli_close($sqlConnection);
}
