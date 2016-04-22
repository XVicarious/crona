<?php
// todo: update SQL
require 'admin_functions.php';
include 'SqlStatements.php';
if (sessionCheck()) {
    $sqlConnection = createSql();
    $administrativeId = $_SESSION['userId'];
    //$dbh = createPDO();
    $permissionQuery = "SELECT company_code, department_id FROM employee_supervisors WHERE user_id = $administrativeId";
    $result = mysqli_query($sqlConnection, $permissionQuery);
    $permissionsArray = [];
    while (list($companyCode, $departmentId) = mysqli_fetch_row($result)) {
        if (!isset($permissionsArray[$companyCode])) {
            $permissionsArray[$companyCode] = [];
        }
        array_push($permissionsArray[$companyCode], $departmentId);
    }
    if (empty($permissionsArray)) { die(); }
    $adminPermissionPart = 'WHERE ';
    $indexNumber = 0;
    $permissionsArrayCount = count($permissionsArray);
    foreach ($permissionsArray as $key => $permission) {
        $permissionSize = count($permission);
        $adminPermissionPart .= "(user_companycode = \"$key\"";
        // if they have permission of department 000, they have permission to everyone at that property
        if (!$permission === '000') {
            $adminPermissionPart .= ' AND (';
            for ($i = 0; $i < $permissionSize; $i++) {
                $adminPermissionPart .= 'user_department = ' . $permission[$i];
                if ($i < $permissionSize - 1) {
                    $adminPermissionPart .= ' OR ';
                }
            }
            $adminPermissionPart .= ')';
        }
        $adminPermissionPart .= ')';
        if ($indexNumber < $permissionsArrayCount - 1) {
            $adminPermissionPart .= ' OR ';
        }
        $indexNumber++;
    }
    $employeeQuery = "SELECT user_id, user_adpid, user_last, user_first, user_companycode, user_department
                      FROM employee_list $adminPermissionPart ORDER BY user_last";
    $result = mysqli_query($sqlConnection, $employeeQuery);
    $resultNumber = mysqli_num_rows($result);
    $a_employeeList = [];
    if ($resultNumber !== 0) {
        // Start generating that table monkey!
        $totalPages = ceil($resultNumber / 10);
        while (list($user_id,$user_adpid,$user_last,$user_first,$user_companycode,$user_department) = mysqli_fetch_row($result)) {
            array_push($a_employeeList, ['id' => $user_id,
                                            'adpid' => $user_adpid,
                                            'name' => "$user_last, $user_first",
                                            'companycode' => "$user_companycode",
                                            'departmentcode' => $user_department]);
        }
    } else {
        echo 'No employees found!';
    }
    $_SESSION['json'] = json_encode($a_employeeList, JSON_NUMERIC_CHECK);
    mysqli_close($sqlConnection);
    header('Location: build_employees.php');
}
