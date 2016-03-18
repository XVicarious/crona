<?php
require '../function.php';
require '../admin/admin_functions.php';
require '../admin/SqlStatements.php';
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
            $stmt = $dbh->prepare(SqlStatements::GET_USER_ID_FROM_USERNAME_EMAIL);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) > 0) {
                $userID = $result[0]['user_id'];
                $stmt = $dbh->prepare(SqlStatements::INSERT_NEW_RESET_STRING);
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
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
    $resetString = $_GET['c'];
    $dbh = createPDO();
    try {
        $success = false;
        $stmt = $dbh->prepare(SqlStatements::GET_RESET_INFORMATION);
        $stmt->bindParam(':resetString', $resetString, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($result) > 0) {
            $result = $result[0];
            $difference = time() - strtotime($result['reset_date']);
            if ($difference >= 86400) {
                $stmt = $dbh->prepare(SqlStatements::DELETE_RESET_BY_STRING);
                $stmt->bindParam(':resetString', $resetString, PDO::PARAM_STR);
                $stmt->execute();
                echo 'The password reset link you gave has expired, please request a new password reset.';
                return;
            } else {
                $uid = $result['reset_uid'];
                $stmt = $dbh->prepare(SqlStatements::SELECT_RANDOM_SECURITY_QUESTION);
                $stmt->bindParam(':userID', $uid, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $result = $result[0];
                $securityQuestion = $result['sque_question'];
                $securityId = $result['sque_id'];
                echo '<div id="rpassword" style="position:absolute;display:block"><form style="background-color:white">
              <input id="uid" type=hidden name="user" value="' . $uid . '"><input type=hidden name="function"
              value="checkReset"><table id="loginForm" style="border:solid thin black;table-layout:fixed;
              font-family:monospace"><tr></tr><tr><td>' . $securityQuestion . "</td><td>
              <input id=\"answer\" name=\"answer\" type=password></td></tr><tr><td>New Password:<input type=hidden
              id=\"resetId\" name=\"resetId\" value=\"$resetString\"><input id=\"qid\" type=hidden name=\"qid\"
              value=\"$securityId\"></td><td><input id=\"pw\" type=password></td></tr><tr><td>Confirm New Password:</td>
              <td><input id=\"pwc\" type=password name=\"pword\"></td></tr><tr><th colspan=\"3\"><input id=\"subby\"
              style=\"width:100%\" type=submit value=\"Submit\"></th></tr></table></form></div>";
            }
        } else {
            echo '*to the tune of the creepy Twilight Zone chime* do do do do, do do do do';
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
