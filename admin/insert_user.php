<?php
function randomPassword($len = 8) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $l = strlen($chars) - 1;
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $chars[rand(0, $l)];
    }
    return $str;
}
function randomSalt($len = 8) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()_+-=";
    $l = strlen($chars) - 1;
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $chars[rand(0, $l)];
    }
    return $str;
}
function generateUsername($sqlConnection, $baseUsername, $number=0) {
    $username = $number ? $baseUsername.$number : $baseUsername;
    $result = mysqli_query($sqlConnection, "SELECT user_name FROM employee_list WHERE user_name = '$username'");
    if (mysqli_num_rows($result) !== 0) {
        generateUsername($sqlConnection, $baseUsername, ++$number);
    }
    return $baseUsername;
}
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    // We allow for up to 10 users to be added at a time, so we will loop over 10 integers!
    for ($i = 0; $i < 11; $i++) {
        // If there isn't one break out of the loop
        echo $i;
        if (!isset($_POST["$i"])) {
            echo "none :/";
            break;
        }
        $a_postUser = json_decode($_POST["$i"]);
        echo $a_postUser;
        $userLast = $a_postUser[0];
        $userFirst = $a_postUser[1];
        $userEmail = $a_postUser[5];
        $userStart = isset($a_postUser[6]) ? $a_postUser[6] : date("Y-m-d",time());
        $userCompany = $a_postUser[2];
        $userDepartment = $a_postUser[3];
        $userADPID = $a_postUser[4];
        $userPassword = randomPassword();
        $salt = randomSalt();
        $userPassword = $salt.sha1($salt.$userPassword);
        $username = generateUsername($sqlConnection, substr($userFirst,0,1).$userLast);
        $query = 'INSERT INTO employee_list (user_name,user_adpid,user_companycode,user_department,user_password,user_first,user_last';
        $queryPartTwo = "VALUES ('$username',$userADPID,'$userCompany',$userDepartment,'$userPassword','$userFirst','$userLast'";
        if (isset($userEmail)) {
            $query .= ',user_emails';
            $queryPartTwo .= ",'$userEmail'";
        }
        if (isset($userStart)) {
            $query .= ',user_start';
            $queryPartTwo .= ",'$userStart'";
        }
        $query .= ') ';
        $queryPartTwo .= ')';
        $query .= $queryPartTwo;
        echo $query;
        mysqli_query($sqlConnection, $query);
    }
    mysqli_close($sqlConnection);
}