<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $administrativeId = $_SESSION['userId'];
    $permissionsQuery = "SELECT user_admin_perms FROM employee_list WHERE user_id = $administrativeId";
    $permissionQueryPart = '';
    $permissionResult = mysqli_query($sqlConnection, $permissionsQuery);
    //echo findExceptions($sqlConnection);
    list($permissionResult) = mysqli_fetch_row($permissionResult);
    if (!isset($permissionResult)) {
        return false;
    }
    if ($permissionResult !== 'all') {
        $a_permission = unserialize($permissionResult);
        $a_companyCode = array_keys($a_permission);
        $permissionQueryPart = 'WHERE ';
        foreach ($a_companyCode as $companyCode) {
            $permissionQueryPart .= "(user_companyCode = '$companyCode'";
            if (count($a_permission[$companyCode]) > 0) {
                $permissionQueryPart .= ' AND (';
            }
            foreach ($a_permission[$companyCode] as $departmentCode) {
                $permissionQueryPart .= "user_department = $departmentCode OR ";
            }
            if (substr($permissionQueryPart, strlen($permissionQueryPart) - 4) === ' OR ') {
                $permissionQueryPart = substr($permissionQueryPart, 0, strlen($permissionQueryPart)-4);
            }
            if (count($a_permission[$companyCode]) > 0) {
                $permissionQueryPart .= ')';
            }
            $permissionQueryPart .= ') OR ';
        }
        $permissionQueryPart = substr($permissionQueryPart, 0, strlen($permissionQueryPart)-4);
    }
    $employeeQuery = "SELECT user_id,user_adpid,user_last,user_first,user_companycode,user_department FROM employee_list $permissionQueryPart ORDER BY user_last";
    $employeeResult = mysqli_query($sqlConnection, $employeeQuery);
    $resultNumber = mysqli_num_rows($employeeResult);
    $a_employeeList = [];
    if ($resultNumber !== 0) {
        // Start generating that table monkey!
        $totalPages = ceil($resultNumber/10);
        array_push($a_employeeList, ["per_page"=>10,"total_entries"=>$resultNumber,"total_pages"=>$totalPages,"page"=>1]);
        array_push($a_employeeList, []);
        while (list($user_id,$user_adpid,$user_last,$user_first,$user_companycode,$user_department) = mysqli_fetch_row($employeeResult)) {
            array_push($a_employeeList[1], ['id'=>$user_id,'adpid'=>$user_adpid,'name'=>"$user_last, $user_first",'companycode'=>$user_companycode,'departmentcode'=>$user_department]);
        }
    } else {
        echo 'No employees found!';
    }
    echo json_encode($a_employeeList, JSON_NUMERIC_CHECK);
    mysqli_close($sqlConnection);
}
