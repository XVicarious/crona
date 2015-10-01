<?php
$name = "Approaching Overtime";
require "../admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $overtimeThreshold = $_POST['threshold'];
    $companiesJSON = $_POST['json'];
    /*
     * The JSON should be formatted as follows:
     * {
     * companies: [[companyCode, [<optional departmentCode>]],[...]]
     * }
     */
    $companiesJSON = json_decode($companiesJSON, true);
}
