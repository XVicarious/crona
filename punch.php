<?php
require 'function.php';
require 'admin/admin_functions.php';
include 'admin/SqlStatements.php';
if ($_POST) {
    $dbh = createPDO();
    try {
        $myUsername = $_POST['uname'];

        $stmt = $dbh->prepare(SqlStatements::GET_USER_CREDENTIALS, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $stmt->bindParam(':username', $myUsername, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            error_log("The query has failed.", 0);
            die();
        } elseif ($result === 0) {
            error_log("The query returned no values.", 0);
            die();
        }
        // make the array easier to work with, this WILL only return one row
        // because usernames are unique.
        $result = $result[0];
        if (validateLogin($_POST['drowp'], $result['user_hash'], $result['user_salt'])) {
            $passwordSetLapse = time() - $result['user_created'];
            if ($passwordSetLapse >= 15742080) {
                $data = http_build_query(['function' => 'sendEmail', 'email' => $result['user_email']]);
                $options = ['http' => ['method' => 'POST', 'content' => $data]];
                $stream = stream_context_create($options);
                $fileOpen = fopen('http://xvss.net/devel/time/forgot/resetutil.php', 'rb', false, $stream);
                toast('Password Expired!  A link to reset your password has been sent to your email!');
                return;
            }
            try {
                $stmt = $dbh->prepare(SqlStatements::GET_SECURITY_QUESTIONS, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
                $stmt->bindParam(':userid', $result['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $userId = $result['user_id'];
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($result) < 3) {
                    session_start();
                    $_SESSION['lastAction'] = time();
                    $_SESSION['userId'] = $userId;
                    echo '$(location).attr("href","https://xvss.net/devel/time/forgot/set_security_questions.php");';
                    return;
                }
                if ($_POST['loginType'] === 'timestamp') {
                    if ($_SERVER['REMOTE_ADDR'] === '40.132.64.225') {
                        date_default_timezone_set('Atlantic/Reykjavik');
                        $now = date('Y-m-d H:i:s');
                        try {
                            $stmt = $dbh->prepare(SqlStatements::SET_INSERT_STAMP, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
                            $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
                            $stmt->bindParam(':now', $now, PDO::PARAM_STR); // Is really of type DATETIME
                            $dbh->beginTransaction(); // don't commit everything right away, does this work with this?
                            $stmt->execute();
                            toast("Timestamp Accepted!", 4000);
                        } catch (Exception $e) {
                            $dbh->rollBack();
                            error_log('Failure: '.$e->getMessage(), 0);
                        }
                    } else {
                        toast("Timestamp NOT Accepted!", 4000);
                    }
                } elseif ($_POST['loginType'] === 'cardAdmin') {
                    if (!isset($_SESSION)) {
                        session_start();
                    }
                    $_SESSION['lastAction'] = time();
                    $_SESSION['userId'] = $userId;
                    echo '$(location).attr("href", "admin");';
                }
            } catch (Exception $e) {
                error_log('Failed: '.$e->getMessage(), 0);
            }
        } else {
            toast("Your usename or password is incorrect.");
        }
    } catch (Exception $e) {
        error_log('Failed: '.$e->getMessage(), 0);
    }
    // Close the database
    $dbh = null;
}
