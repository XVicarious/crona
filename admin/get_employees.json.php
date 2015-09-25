<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $administrativeId = $_SESSION['userId'];
    $permissionQuery = "SELECT company_code, department_id FROM employee_supervisors WHERE user_id = $administrativeId";
    $result = mysqli_query($sqlConnection, $permissionQuery);
    $permissionsArray = [];
    while (list($companyCode, $departmentId) = mysqli_fetch_row($result)) {
        if (!isset($permissionsArray[$companyCode])) {
            $permissionsArray[$companyCode] = [];
        }
        array_push($permissionsArray[$companyCode], $departmentId);
    }
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
        array_push($a_employeeList, ["per_page" => 10,
                                     "total_entries" => $resultNumber,
                                     "total_pages" => $totalPages,
                                     "page" => 1]);
        array_push($a_employeeList, []);
        while (list($user_id,$user_adpid,$user_last,$user_first,$user_companycode,$user_department) = mysqli_fetch_row($result)) {
            array_push($a_employeeList[1], ['id' => $user_id,
                                            'adpid' => $user_adpid,
                                            'name' => "$user_last, $user_first",
                                            'companycode' => $user_companycode,
                                            'departmentcode' => $user_department]);
        }
    } else {
        echo 'No employees found!';
    }
    echo json_encode($a_employeeList, JSON_NUMERIC_CHECK);
    mysqli_close($sqlConnection);
}
