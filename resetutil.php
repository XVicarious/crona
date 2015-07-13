<?php
require 'function.php';
require 'admin/admin_functions.php';
$sqlConnection = createSql();
$function = $_POST['function'];
if (isset($function)) {
    if ($function === 'checkReset') {
        $resetId = $_POST['resetId'];
        $password = $_POST['pword'];
        $qanswer = strtolower($_POST['answer']);
        $qid = $_POST['qid'];
        $userId = $_POST['user'];
        $query = "SELECT eque_answer FROM employee_questions WHERE eque_number = $qid AND eque_user = $userId";
        $result = mysqli_query($sqlConnection, $query);
        list($lowerAnswer) = mysqli_fetch_row($result);
        if (sha1($qanswer) === $lowerAnswer) {
            $salt = randomSalt();
            $password = $salt . $password;
            $password = sha1($password);
            $time = time();
            $query = "INSERT INTO user_hashes (uhsh_user, uhsh_hash, uhsh_created) VALUES ($userId, $password, $time)";
            mysqli_query($sqlConnection, $query);
            $query = "INSERT INTO user_salts (uslt_user, uslt_salt) VALUES ($userId, '$salt')";
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
            $message = "Dear user,\nPlease follow the following link to reset your password to the timestamp system.
                        \n$resetLink\nThis link will only be active for 24 hours.";
            $headers = "From: Hart Hotels Timestamp <administrator@harthotels.com>\nTo-Sender:\nX-Mailer:PHP\nReply-To
                        :bmaurer@harthotels.com\nReturn-Path:bmaurer@harthotels.com\nContent-Type:text/html;
                        charset=iso-8859-1";
            $subject = 'Password Recovery for Hart Hotels Timestamp';
            mail($email, $subject, $message, $headers);
        }
        //echo "If a user with the email <b>$email</b> exists, an email has been dispatched with a link to
        //      reset your password.";
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
            echo 'The password reset link you gave has expired.  Please to go
                  <a href="http://xvicario.us/time/reset_password.php?r=go">http://xvicario.us/time/reset_password.php
                  ?r=go</a> to get a new link.';
            return;
        }
        $random = rand(0, 2);
        $securityId = "sec_$random";
        $query = "SELECT sque_id, sque_question FROM security_questions WHERE sque_id IN
                  (SELECT eque_number FROM employee_questions WHERE eque_user = $uid) LIMIT 1 OFFSET $random";
        $result = mysqli_query($sqlConnection, $query);
        echo $query;
        list($securityId, $securityQuestion) = mysqli_fetch_row($result);
        echo '<div id="rpassword" style="position:absolute;display:block"><form style="background-color:white">
              <input id="uid" type=hidden name="user" value="' . $uid . '"><input type=hidden name="function"
              value="checkReset"><table id="loginForm" style="border:solid thin black;table-layout:fixed;
              font-family:monospace"><tr></tr><tr><td>' . $securityQuestion . "</td><td>
              <input id=\"answer\" name=\"answer\" type=password></td></tr><tr><td>New Password:<input type=hidden
              id=\"resetId\" name=\"resetId\" value=\"$resetId\"><input id=\"qid\" type=hidden name=\"qid\"
              value=\"$securityId\"></td><td><input id=\"pw\" type=password></td></tr><tr><td>Confirm New Password:</td><td>
              <input id=\"pwc\" type=password name=\"pword\"></td></tr><tr><th colspan=\"3\"><input id=\"subby\"
              style=\"width:100%\" type=submit value=\"Submit\"></th></tr></table></form></div>";
    } else {
        echo 'Invalid reset link.';
    }
} elseif (!isset($_GET['c'])) {
    echo '<div id="semail" class="container">
           <div class="row">
            <div class="input-field col s12 l6 offset-l3">
             <i class="mdi-communication-email prefix orange-text darken-1"></i>
             <input placeholder="email@email.com" id="email" type="text" name="email"/>
            </div>
           </div>
           <div class="row">
            <div class="col s12 l6 offset-l3">
             <div class="center">
              <a href="#" id="subby" class="cyan lighten-1 waves-effect waves-light btn">Send Email<i class="mdi-content-send right"></i></a>
             </div>
            </div>
           </div>
          </div>';
}
mysqli_close($sqlConnection);
