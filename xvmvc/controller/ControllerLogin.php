<?php

namespace xvmvc\controller;

class ControllerLogin extends Controller
{
    public function stampTime()
    {
        $userId = $this->validateLogin();
        if ($userId === false) {
            return false;
        }
        $currentTime = date('Y-m-d H:i:s');
        $dbh = createPDO();
        try {
            $statement = $dbh->prepare(\SqlStatements::SET_INSERT_STAMP, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
            $statement->bindParam(':userid', $userId, \PDO::PARAM_INT);
            $statement->bindParam(':now', $currentTime, \PDO::PARAM_STR);
        } catch (\Exception $exception) {
            $dbh->rollBack();
            error_log('Failure: '.$exception->getMessage(), 0);
            return false;
        }
        $dbh = null;
        return true;
    }
    public function portalLogin($type)
    {
        $userId = $this->validateLogin();
        if ($userId === false) {
            return false;
        }
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['lastAction'] = time();
        $_SESSION['userId'] = $userId;
        if ($type === 'admin') {
            $_SESSION['operationMode'] = 0;
        } elseif ($type === 'view') {
            $_SESSION['operationMode'] = 1;
        } else {
            // illegal mode
            return false;
        }
        return true;
    }
    private function validateLogin()
    {
        $dbh = createPDO();
        try {
            $myUsername = $_POST['username'];
            $statement = $dbh->prepare(\SQLStatements::GET_USER_CREDENTIALS, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
            $statement->bindParam(':username', $myUsername, \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            if (is_array($result)) {
                $result = $result[0];
                if (function_exists('hash') && in_array('sha1', hash_algos())) {
                    $passwordGood = ($result['user_hash'] === hash('sha1', $result['user_salt'] . $_POST['password']));
                } else {
                    $passwordGood = ($result['user_hash'] === sha1($result['user_salt'] . $_POST['password']));
                }
                if (!$passwordGood) {
                    return false;
                }
                $passwordSetLapse = time() - $result['user_created'];
                // todo: make password expiry time configurable
                if ($passwordSetLapse >= 15742080) {
                    // todo: handle password expiration
                    return false;
                }
                $statement = $dbh->prepare(\SQLStatements::GET_SECURITY_QUESTIONS, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
                $userId = $result['user_id'];
                $statement->bindParam(':userid', $userId, \PDO::PARAM_INT);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                if (!is_array($result) || count($result) < 3) {
                    // todo: handle no security questions answered
                    return false;
                }
                $dbh = null;
                return $userId;
            }
        } catch (\Exception $exception) {
            $dbh = null;
            error_log('Failed: '.$exception->getMessage(), 0);
        }
        return false;
    }
}
