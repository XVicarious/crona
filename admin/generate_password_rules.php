<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    $regexString = $_POST['requires'];
    $passwordLength = $_POST['minLength'];
    $a_rules = explode(',', $regexString);
}
