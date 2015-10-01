<?php
require 'function.php';
require 'admin/admin_functions.php';
if ($_POST) {
    $dbh = createPDO();
    try {
        $myUsername = $_POST['uname'];
        $query = "SELECT employee_list.user_id,
                     user_hashes.uhsh_hash AS user_hash,
                     user_hashes.uhsh_created AS user_created,
                     user_salts.uslt_salt AS user_salt,
                     user_emails.ueml_email AS user_email
              FROM employee_list
                  LEFT JOIN user_hashes ON employee_list.user_id = user_hashes.uhsh_user
                  LEFT JOIN user_salts ON employee_list.user_id = user_salts.uslt_user
                  LEFT JOIN user_emails ON employee_list.user_email_primary = user_emails.ueml_id
              WHERE employee_list.user_name = :username";
        $stmt = $dbh->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $stmt->bindParam(':username', $myUsername, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            javascriptLog("The query has failed.");
            die();
        } elseif ($result === 0) {
            javascriptLog("The query returned no values.");
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
                $fileOpen = fopen('http://xvss.net/time/resetutil.php', 'rb', false, $st);
                echo 'Password Expired!  A link to reset your password has been sent to your email!';
                return;
            }
            try {
                $query = 'SELECT eque_number FROM employee_questions WHERE eque_user = :userid';
                $stmt = $dbh->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $stmt->bindParam(':userid', $result['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $userId = $result['user_id'];
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($result) < 3) {
                    session_start();
                    $_SESSION['lastAction'] = time();
                    echo '<script>$(location).attr("href","https://xvss.net/devel/time/forgot/set_security_questions.php")</script>';
                    return;
                }
                if ($_POST['loginType'] === 'timestamp') {
                    if ($_SERVER['REMOTE_ADDR'] === '40.132.64.225') {
                        date_default_timezone_set('Atlantic/Reykjavik');
                        $now = date('Y-m-d H:i:s');
                        try {
                            $query = 'INSERT INTO timestamp_list (user_id_stamp, tsl_stamp)
                                  VALUES (:userid, :now)';
                            $stmt = $dbh->prepare($query, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
                            $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
                            $stmt->bindParam(':now', $now, PDO::PARAM_STR); // Is really of type DATETIME
                            $dbh->beginTransaction(); // don't commit everything right away, does this work with this?
                            $stmt->execute();
                            // todo: Figure out better way to do this
                            echo '<script>Materialize.toast("Timestamp Accepted!")</script>';
                        } catch (Exception $e) {
                            $dbh->rollBack();
                            javascriptLog('Failure: '.$e->getMessage());
                        }
                    } else {
                        // todo: Figure out better way to do this
                        echo '<script>Materialize.toast("Timestamp NOT Accepted!")</script>';
                    }
                } elseif ($_POST['loginType'] === 'cardAdmin') {
                    session_start();
                    $_SESSION['lastAction'] = time();
                    $_SESSION['user_id'] = $userId;
                    // redirect to admin
                }
            } catch (Exception $e) {
                javascriptLog('Failed: '.$e->getMessage());
            }
        }
    } catch (Exception $e) {
        javascriptLog('Failed: '.$e->getMessage());
    }
    // Close the database
    $dbh = null;
}
