<?php
require '../function.php';
require '../admin/admin_functions.php';
require '../admin/SqlStatements.php';
$sqlConnection = createSql();
$function = $_POST['function'];
if (isset($function)) {
    if ($function === 'checkReset') {
        $dbh = createPDO();
        $success = false;
        try {
            $stmt = $dbh->prepare(SqlStatements::GET_USER_SECURITY_ANSWER, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $qid = $_POST['qid'];
            $userId = $_POST['user'];
            $stmt->bindParam(':questionNumber', $qid, PDO::PARAM_INT);
            $stmt->bindParam(':userID', $qid, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $qanswer = strtolower($_POST['answer']);
            if (sha1($qanswer) === $result[0]['eque_answer']) {
                $stmt = $dbh->prepare(SqlStatements::INSERT_NEW_USER_PASSWORD, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
                $stmt->bindParam(':userID', $userId, PDO::PARAM_INT);
                $salt = randomSalt();
                $password = sha1($salt . $_POST['pword']);
                $stmt->bindParam(':hashWord', $password, PDO::PARAM_LOB);
                $stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
                $resetString = $_POST['resetId'];
                $stmt->bindParam(':resetString', $resetString, PDO::PARAM_STR);
                $stmt->execute();
                echo 'Password changed successfully!';
            } else {
                echo 'The answer was not correct!';
            }
            $success = true;
        } catch (PDOException $e) {
            error_log($e->getMessage(), 0);
        } catch (Exception $e) {
            error_log($e->getMessage(), 0);
        } finally {
            $dbh = null;
            if (!$success) {
                die();
            }
        }
    } elseif ($function === 'sendEmail') {
        $email = $_POST['email'];
        $username = $_POST['username'];
        $resetString = randomString();
        $resetLink = "http://xvss.net/time/forgot/?c=$resetString";
        $dbh = createPDO();
        $success = false;
        try {
            $stmt = $dbh->prepare(SqlStatements::GET_USER_ID_FROM_USERNAME_EMAIL, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                $userID = $result[0]['user_id'];
                $stmt = $dbh->prepare(SqlStatements::INSERT_NEW_RESET_STRING);
                $stmt->bindParam(':resetString', $resetString, PDO::PARAM_STR);
                $stmt->execute();
                $message = "Dear $username,\nPlease follow the following link to reset your password to the timestamp
                            system.\n$resetLink\nThis link will only be active for 24 hours.";
                $headers = "From: Hart Hotels Timestamp <administrator@harthotels.com>\nTo-Sender:\nX-Mailer:PHP
                            \nReply-To:bmaurer@harthotels.com\nReturn-Path:bmaurer@harthotels.com
                            \nContent-Type:text/html;charset=iso-8859-1";
                $subject = 'Password Recovery for Hart Hotels Timestamp';
                mail($email, $subject, $message, $headers);
            } else {
                echo 'username and email combination was not found!';
            }
            $success = true;
        } catch (PDOException $e) {
            error_log($e->getMessage(), 0);
        } catch (Exception $e) {
            error_log($e->getMessage(), 0);
        } finally {
            $dbh = null;
            if (!$success) {
                die();
            }
        }
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
                  <a href="http://xvicario.us/time/forgot/?r=go">http://xvicario.us/time/forgot/
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
    // moved to user_email.html
    // figure out how you want to put it back here.
    // I feel like JavaScript and $().load would be good.
}
mysqli_close($sqlConnection);
