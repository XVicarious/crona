<?php
require "admin_functions.php";
$sqlConnection = createSql();
if (sessionCheck()) {
    // We allow for up to 10 users to be added at a time, so we will loop over 10 integers!
    for ($i = 0; $i < 11; $i++) {
        // If there isn't one break out of the loop
        if (!isset($_POST["$i"])) {
            break;
        }
        $a_postUser = json_decode($_POST["$i"]);
        $userLast = $a_postUser[0];
        $userFirst = $a_postUser[1];
        $userEmail = $a_postUser[5];
        $userStart = isset($a_postUser[6]) ? $a_postUser[6] : date("Y-m-d", time());
        $userCompany = $a_postUser[2];
        $userDepartment = $a_postUser[3];
        $userADPID = $a_postUser[4];
        $userPassword = randomSalt(false);
        $salt = randomSalt();
        $userPassword = sha1($salt . $userPassword);
        $username = generateUsername($sqlConnection, substr($userFirst, 0, 1) . $userLast);

        $checkEmailQuery = "SELECT ueml_id FROM user_emails WHERE ueml_email='$userEmail'";
        $result = mysqli_query($sqlConnection, $checkEmailQuery);
        if (mysqli_num_rows($result) > 0) {
            $userEmail = mysqli_fetch_assoc($result); // what does this return again?
            $userEmail = $userEmail['ueml_id'];
        } else {
            $emailQuery = "INSERT INTO user_emails (ueml_email) VALUES ('$userEmail')
                           SELECT ueml_id FROM user_emails WHERE ueml_email = '$userEmail'";
            $result = mysqli_query($sqlConnection, $emailQuery);
            $userEmail = mysqli_fetch_assoc($result); // errr?
            $userEmail = $userEmail['ueml_id'];
        }
        $time = time();
        $query = "INSERT INTO employee_list (user_name,user_adpid,user_companycode,user_department,user_first,user_last)
                  VALUES ($username,$userADPID,$userCompany,$userDepartment,$userFirst,$userLast);
                  INSERT INTO user_hashes (uhsh_user,uhsh_hash,uhsh_created) VALUES (
                  (SELECT user_id FROM employee_list WHERE user_name = '$username'), $userPassword, $time);";
        echo $query;
        /*$query = 'INSERT INTO employee_list (user_name,user_adpid,user_companycode,user_department,user_password,
                  user_first,user_last';
        $queryPartTwo = "VALUES ('$username',$userADPID,'$userCompany',$userDepartment,'$userPassword','$userFirst',
                         '$userLast'";
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
        mysqli_query($sqlConnection, $query);*/
    }
    mysqli_close($sqlConnection);
}
