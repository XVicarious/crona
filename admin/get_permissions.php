<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $userId = $_SESSION['userId'];
    $query = "SELECT company_code, department_id FROM employee_supervisors WHERE user_id = $userId";
    $result = mysqli_query($sqlConnection, $query);
    $permissionArray = [];
    while(list($companyCode, $departmentId) = mysqli_fetch_row($result)) {
        array_push($permissionArray, [$companyCode, $departmentId]);
    }
    echo json_encode($permissionArray);
}
