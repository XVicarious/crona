<?php
session_start();
require 'admin_functions.php';
$sqlConnection = createSql();
$administrativeId = $_SESSION['userId'];
if (isset($administrativeId)) {
    $adminResults = mysqli_query($sqlConnection, "SELECT company_code FROM employee_supervisors
                                                  WHERE user_id = $administrativeId");
    if (mysqli_num_rows($adminResults) !== 0) {
        $adminPermissions = [];
        while (list($compCode) = mysqli_fetch_row($adminResults)) {
            array_push($adminPermissions, "$compCode");
        }
        $companyCodes = ["48N" => "HNB Venture Ptrs LLC",
                         "49C" => "Hampton Inn Boston/Natick",
                         "49D" => "Crowne Plaza Boston",
                         "49E" => "Holiday Inn Somervil",
                         "7IS" => "Skybokx 109 Natick",
                         "9NI" => "Hart Hotels DLC, LLC",
                         "ANY" => "FLH Development, LLC",
                         "FB1" => "Madison Beach Hotel",
                         "GE3" => "Distinctive Hospitality Group",
                         "GG8" => "Seneca Market 1",
                         "H4G" => "DDH Hotel Mystic LLC",
                         "HUG" => "ATA Associates",
                         "HXH" => "Portland Harbor Hotel",
                         "KZH" => "Clayton Harbor Hotel",
                         "L99" => "Lenroc, L.P.",
                         "NPJ" => "WPH Midtown Associates",
                         "NPM" => "WPH Airport Associates",
                         "PPP" => "Golden Triangle Associates",
                         "Q56" => "Hart Management Group",
                         "RK3" => "HBK Restaurant LLC",
                         "ZVT" => "Twenty Flint Rd LLC"];
        $ak_companyCodes = array_keys($companyCodes);
        foreach ($ak_companyCodes as $code) {
            if (!in_array($code, $adminPermissions)) {
                unset($companyCodes[$code]);
            }
        }
        $ak_companyCodes = array_keys($companyCodes);
        echo '<select id="companyCode" class="browser-default">';
        foreach ($ak_companyCodes as $code) {
            $t_name = $companyCodes[$code];
            echo "<option value=\"$code\">[$code] $t_name</option>";
        }
        echo '</select>';
    }
}
