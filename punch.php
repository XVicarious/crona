<?php
require 'function.php';
if ($_POST) {
    //todo: convert to new sql system
    $conn = mysqli_connect("localhost", "bmaurer_pciven", "3al12of4ut25", "bmaurer_hhemployee");
    $myu = $_POST['uname'];
    //$query = "SELECT user_id, user_name, user_password, user_password_set, user_emails FROM employee_list WHERE
              //employee_list.user_name = \"$myu\"";
    $query = "SELECT employee_list.user_id,
                     user_hashes.uhsh_hash AS user_hash,
                     user_hashes.uhsh_created AS user_created,
                     user_salts.uslt_salt AS user_salt,
                     user_emails.ueml_email AS user_email
              FROM employee_list
                  LEFT JOIN user_hashes ON employee_list.user_id = user_hashes.uhsh_user
                  LEFT JOIN user_salts ON employee_list.user_id = user_salts.uslt_user
                  LEFT JOIN user_emails ON employee_list.user_email_primary = user_emails.ueml_id
              WHERE employee_list.user_name = \"$myu\"";
    $result = mysqli_query($conn, $query);
    if ($result !== false) {
        if (mysqli_num_rows($result) !== 0) {
            //list($uid, $uname, $upas, $udate, $uemail) = mysqli_fetch_row($result);
            list($uid, $userHash, $userCreated, $userSalt, $userEmail) = mysqli_fetch_row($result);
            if (validateLogin($_POST['drowp'], $userHash, $userSalt)) {
                $passwordSetLapse = time() - $userCreated;
                //todo: make password expiration configurable
                if ($passwordSetLapse >= 15742080) {
                    $data = http_build_query(['function' => 'sendEmail', 'email' => $userEmail]);
                    $opts = ['http' => ['method' => 'POST', 'content' => $data]];
                    $st = stream_context_create($opts);
                    $fp = fopen('http://xvss.net/time/resetutil.php', 'rb', false, $st);
                    echo 'Password Expired!  Reset link set to your email.';
                    return;
                }
                $query = 'SELECT eque_number FROM employee_questions WHERE eque_user = ' . $uid;
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) < 3) {
                    session_start();
                    $_SESSION["lastAction"] = time();
                    $_SESSION["userId"] = $uid;
                    echo '<script>$(location).attr("href","http://xvss.net/devel/time/set_security_questions.php")</script>';
                    return;
                }
                if ($_POST["loginType"] === "timestamp") {
                    if ($_SERVER['REMOTE_ADDR'] === '40.132.64.225') {
                        date_default_timezone_set('Atlantic/Reykjavik');
                        $now = date("Y-m-d H:i:s");
                        $iquery = "INSERT INTO timestamp_list (user_id_stamp,timestamp_list.datetime)
                                   VALUES ($uid,'$now')";
                        mysqli_query($conn, $iquery);
                        echo '<div id="accepted"></div>';
                    } else {
                        echo '<div id="not-accepted"></div>';
                    }
                } elseif ($_POST["loginType"] === "cardAdmin") {
                    session_start();
                    $_SESSION["lastAction"] = time();
                    $_SESSION["userId"] = $uid;
                    echo "<div id=\"a\"></div>";
                } else {
                    date_default_timezone_set('America/New_York');
                    session_start();
                    $_SESSION["lastAction"] = time();
                    $_SESSION["userId"] = $uid;
                    setcookie("xvtss", "$uid", time() + 1200);
                    echo "<div id=\"b\"></div>";
                }
            } else {
                echo "<div id=\"badup\"></div>";
            }
        } else {
            echo "<div id=\"badup\"></div>";
        }
    } else {
        echo 'SEVERE ERROR: PLEASE REPORT TO ADMINISTRATOR';
        echo "<div id=\"badup\"></div>";
    }
    mysqli_close($conn);
}
