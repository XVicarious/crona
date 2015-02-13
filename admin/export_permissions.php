<?php
session_start();
require 'admin_functions.php';
$sqlConnection = createSql();
$administrativeId = $_SESSION['userId'];
if (isset($administrativeId)) {
    $adminResults = mysqli_query($sqlConnection, "SELECT user_admin_perms FROM employee_list WHERE user_id = $administrativeId");
    if (mysqli_num_rows($adminResults) !== 0) {
        list($sa_adminperms) = mysqli_fetch_row($adminResults);
        // todo: This needs to be better organized.  Searching for it?  Seems silly.
        $companyCodes = ["48N"=>"HNB Venture Ptrs LLC","49C"=>"Hampton Inn Boston/Natick","49D"=>"Crowne Plaza Boston","49E"=>"Holiday Inn Somervil","7IS"=>"Skybokx 109 Natick","9NI"=>"Hart Hotels DLC, LLC","ANY"=>"FLH Development, LLC","FB1"=>"Madison Beach Hotel","GE3"=>"Distinctive Hospitality Group","GG8"=>"Seneca Market 1","H4G"=>"DDH Hotel Mystic LLC","HUG"=>"ATA Associates","HXH"=>"Portland Harbor Hotel","KZH"=>"Clayton Harbor Hotel","L99"=>"Lenroc, L.P.","NPJ"=>"WPH Midtown Associates","NPM"=>"WPH Airport Associates","PPP"=>"Golden Triangle Associates","Q56"=>"Hart Management Group","RK3"=>"HBK Restaurant LLC","ZVT"=>"Twenty Flint Rd LLC"];
        $ak_companyCodes = array_keys($companyCodes);
        if ($sa_adminperms === '') {

            return false;
        }
        if ($sa_adminperms !== 'all') {
            $a_permissions = unserialize($sa_adminperms);
            $a_permissionCodes = array_keys($a_permissions);
            foreach ($ak_companyCodes as $code) {
                if (!array_key_exists($code,$a_permissions)) {
                    unset($companyCodes[$code]);
                }
            }
            $ak_companyCodes = array_keys($companyCodes);
        }
        echo '<select id="companyCode">';
        foreach($ak_companyCodes as $c) {
            $t_name = $companyCodes[$c];
            echo "<option value=\"$c\">[$c] ".substr($t_name,0,11)."...</option>";
        }
        echo '</select>';
    }
}