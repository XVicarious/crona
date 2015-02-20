<?php
function randomSalt($len = 8)
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789`~!@#$%^&*()_+-=";
    $stringLength = strlen($chars) - 1;
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $chars[rand(0, $stringLength)];
    }
    return $str;
}

function createHash($string, $hashMethod = 'sha1', $saltLength = 8)
{
    $salt = randomSalt($saltLength);
    if (function_exists('hash') && in_array($hashMethod, hash_algos())) {
        return hash($hashMethod, $salt . $string);
    }
    return sha1($salt . $string);
}

function randomString()
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $stringLength = strlen($chars) - 1;
    $str = '';
    for ($i = 0; $i < 32; ++$i) {
        $str .= $chars[rand(0, $stringLength)];
    }
    return $str;
}

$a_securityQuestions = ["What city was your mother born in?",
                        "What is the name of the street you grew up on?",
                        "What is then name of your first grade teacher?",
                        "What is your father's middle name?",
                        "What is your favorite color?",
                        "What is your favorite food?",
                        "What was the make and model of your first car?",
                        "What was the name of your childhood best friend?",
                        "What was the name of your first pet?",
                        "What was your first phone number?",
                        "Where did you go to primary school?",
                        "Where did you grow up?",
                        "Who was your first boss?"];
require 'admin/admin_functions.php';
$sqlConnection = createSql();
$function = $_POST['function'];
if (isset($function)) {
    if ($function === 'checkReset') {
        $resetId = $_POST['resetId'];
        $password = $_POST['pword'];
        $qanswer = $_POST['answer'];
        $qid = $_POST['qid'];
        $userId = $_POST['user'];
        $securityId = "sec_$qid";
        $query = "SELECT $securityId FROM employee_security WHERE sec_user_id = $userId";
        $result = mysqli_query($sqlConnection, $query);
        list($sa_qa) = mysqli_fetch_row($result);
        $a_qa = unserialize($sa_qa);
        if (sha1($qanswer) === $a_qa[1]) {
            $salt = randomSalt();
            $password = $salt . $password;
            $password = sha1($password);
            $password = $salt . $password;
            $query = "UPDATE employee_list SET user_password = '$password', user_password_set = DEFAULT
                      WHERE user_id = $userId";
            mysqli_query($sqlConnection, $query);
            $query = "DELETE FROM reset_list WHERE reset_string = '$resetId'";
            mysqli_query($sqlConnection, $query);
            echo 'Password changed successfully!<br><script>$(location).attr("href","http://xvss.net/time");</script>';
        } else {
            echo 'The answer was not correct!  Try again!';
        }
    } elseif ($function === 'sendEmail') {
        $email = $_POST['email'];
        $resetString = randomString();
        $resetLink = "http://xvss.net/time/reset_password.php?c=$resetString";
        $query = "SELECT user_id FROM employee_list WHERE user_emails = '$email'";
        $result = mysqli_query($sqlConnection, $query);
        if (mysqli_num_rows($result) !== 0) {
            list($uid) = mysqli_fetch_row($result);
            $query = "DELETE FROM reset_list WHERE reset_uid = $uid";
            mysqli_query($sqlConnection, $query);
            $query2 = "INSERT INTO reset_list (reset_uid, reset_string) VALUES ($uid, '$resetString')";
            mysqli_query($sqlConnection, $query2);
            $message = "Dear user,\nPlease follow the following link to reset your password to the timestamp system.\n$resetLink\nThis link will only be active for 24 hours.";
            $headers = "From: Hart Hotels Timestamp <administrator@harthotels.com>\nTo-Sender:\nX-Mailer:PHP\nReply-To:bmaurer@harthotels.com\nReturn-Path:bmaurer@harthotels.com\nContent-Type:text/html; charset=iso-8859-1";
            $subject = 'Password Recovery for Hart Hotels Timestamp';
            @mail($email, $subject, $message, $headers);
        }
        echo "If a user with the email <b>$email</b> exists, an email has been dispatched with a link to reset your password.";
    }
} elseif (isset($_GET['c'])) {
    $resetId = $_GET['c'];
    $query = "SELECT reset_uid,reset_string,reset_date FROM reset_list WHERE reset_string = '$resetId'";
    $result = mysqli_query($sqlConnection, $query);
    if (mysqli_num_rows($result) !== 0) {
        list($uid, $resetId, $sdate) = mysqli_fetch_row($result);
        $difference = time() - strtotime($sdate);
        if ($difference >= 86400) {
            $query = "DELETE FROM reset_list WHERE reset_string = '$resetId'";
            mysqli_query($sqlConnection, $query);
            echo 'The password reset link you gave has expired.  Please to go <a href="http://xvicario.us/time/reset_password.php?r=go">http://xvicario.us/time/reset_password.php?r=go</a> to get a new link.';
            return;
        }
        $random = rand(1, 3);
        $securityId = "sec_$random";
        $query = "SELECT $securityId FROM employee_security WHERE sec_user_id = $uid";
        $result = mysqli_query($sqlConnection, $query);
        list($sa_qa) = mysqli_fetch_row($result);
        $a_qa = unserialize($sa_qa);
        echo '<div id="rpassword" style="position:absolute;display:block"><form style="background-color:white"><input id="uid" type=hidden name="user" value="' . $uid . '"><input type=hidden name="function" value="checkReset"><table id="loginForm" style="border:solid thin black;table-layout:fixed;font-family:monospace"><tr></tr><tr><td>' . $a_securityQuestions[$a_qa[0]] . "</td><td><input id=\"answer\" name=\"answer\" type=password></td></tr><tr><td>New Password:<input type=hidden id=\"resetId\" name=\"resetId\" value=\"$resetId\"><input id=\"qid\" type=hidden name=\"qid\" value=\"$random\"></td><td><input id=\"pw\" type=password></td></tr><tr><td>Confirm New Password:</td><td><input id=\"pwc\" type=password name=\"pword\"></td></tr><tr><th colspan=\"3\"><input id=\"subby\" style=\"width:100%\" type=submit value=\"Submit\"></th></tr></table></form></div>";
    } else {
        echo 'Invalid reset link.';
    }
} elseif (!isset($_GET['c'])) {
    echo '<div id="semail" style="position:absolute;display:block"><form style="background-color:white"><input type=hidden name="function" value="sendEmail"><table id="emailtable" style="border:solid thin black;table-layout:fixed;font-family:monospace"><tr><td>Email Address:</td><td><input id="email" type=text name="email"/></td></tr><tr><th colspan="2"><input id="subby" style="width:100%" type="submit" value="submit"></th></tr></table></form></div>';
}
mysqli_close($sqlConnection);
