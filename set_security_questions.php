<?php
session_start();
if ($_SESSION["lastAction"] + 10 * 60 < time()) {
    session_destroy();
    echo "<script>$(location).attr(\"href\",\"http://xvicario.us/time?timeout=1\")</script>";
}
$_SESSION["lastAction"] = time();
?>
<html>
<head>
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/jquery-ui.theme.css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <script src="js/lib/jquery.js"></script>
    <script src="js/lib/jquery-ui.js"></script>
    <script src="./js/security.js"></script>
</head>
<body>
<?php
$userId = $_SESSION['userId'];
if (isset($userId)) {
    // Please add to the END of this list to not screw everyone up
    $a_securityQuestions = ["What city was your mother born in?", "What is the name of the street you grew up on?", "What is then name of your first grade teacher?", "What is your father's middle name?", "What is your favorite color?", "What is your favorite food?", "What was the make and model of your first car?", "What was the name of your childhood best friend?", "What was the name of your first pet?", "What was your first phone number?", "Where did you go to primary school?", "Where did you grow up?", "Who was your first boss?"];
    require 'admin/admin_functions.php';
    $sqlConnection = createSql();
    /*if ($_GET['s'] === 'partial') {
        $query = "SELECT sec_1,sec_2,sec_3 FROM employee_security WHERE sec_user_id = $userId";
        $result = mysqli_query($sqlConnection, $query);
        list($s1,$s2,$s3) = mysqli_fetch_row($result);
        $s1 = $s1 !== '' ? unserialize($s1) : [0,''];
        $s2 = $s2 !== '' ? unserialize($s2) : [0,''];
        $s3 = $s3 !== '' ? unserialize($s2) : [0,''];
        $a_questions = [$s1,$s2,$s3];
    }
    $a_questions = [$s1,$s2,$s3];*/
    $h_sel1 = '<select id="s1" name="securityQuestion1">';
    $h_sel2 = '<select id="s2" name="securityQuestion2">';
    $h_sel3 = '<select id="s3" name="securityQuestion3">';
    $html_select = '';
    for ($i = 0; $i < count($a_securityQuestions); ++$i) {
        $html_select .= "<option value=\"$i\">" . $a_securityQuestions[$i] . '</option>';
    }
    $html_select .= '</select>';
    echo '<form id="changeSecurity">' . $h_sel1 . $html_select . '<input id="s1i" name="s1i" type=password><input id="s1ic" name="s1ic" type=password><br>' . $h_sel2 . $html_select . '<input id="s2i" name="s2i" type=password><input id="s2ic" name="s2ic" type=password><br>' . $h_sel3 . $html_select . '<input id="s3i" name="s3i" type=password><input id="s3ic" name="s3ic" type=password><br><input id="submit" type=button value="Submit"></form>';
    mysqli_close($sqlConnection);
}
?>
</body>
</html>