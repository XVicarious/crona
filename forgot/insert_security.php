<?php
session_start();
$question1 = $_POST['s1'];
$answer1 = sha1(strtolower($_POST['s1i']));
$question2 = $_POST['s2'];
$answer2 = sha1(strtolower($_POST['s2i']));
$question3 = $_POST['s3'];
$answer3 = sha1(strtolower($_POST['s3i']));
$userId = $_SESSION['userId'];
require '../admin/admin_functions.php';
require '../admin/SqlStatements.php';
$sqlConnection = createSql();
$query = "INSERT INTO employee_questions (eque_number, eque_answer, eque_user) VALUES ($question1, '$answer1', $userId),
          ($question2, '$answer2', $userId), ($question3, '$answer3', $userId)";
mysqli_query($sqlConnection, $query);
mysqli_close($sqlConnection);
$dbh = createPDO();
try {
    $stmt = $dbh->prepare(SqlStatements::INSERT_SECURITY_ANSWERS);
    $stmt->bindParam(':userID', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':questionOne', $question1, PDO::PARAM_INT);
    $stmt->bindParam(':questionTwo', $question2, PDO::PARAM_INT);
    $stmt->bindParam(':questionThree', $question3, PDO::PARAM_INT);
    $stmt->bindParam(':answerOne', $answer1, PDO::PARAM_STR);
    $stmt->bindParam(':answerTwo', $answer2, PDO::PARAM_STR);
    $stmt->bindParam(':answerThree', $answer3, PDO::PARAM_STR);
    $stmt->execute();
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
