<?php
session_start();
if ($_SESSION["lastAction"] + 10 * 60 < time()) {
    session_destroy();
    echo ",\\";
}
$_SESSION["lastAction"] = time();
?>
<html>
<head>
    <link rel="stylesheet" href="../css/materialize.css">
    <link rel="stylesheet" href="../css/material-extra.css">
    <link rel="stylesheet" href="../css/sticky-footer.css">
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/materialize.js"></script>
    <script src="../js/lib/jquery-ui.js"></script>
    <script src="../js/security.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>
<body>
<header class="login">
    <nav class="orange">
        <div class="nav-wrapper">
            <a class="page-title flow-text">Chrona Timestamp</a>
        </div>
    </nav>
</header>
<main class="login">
    <?php
    $userId = $_SESSION['userId'];
    if (isset($userId)) {
        require '../admin/admin_functions.php';
        require '../admin/SqlStatements.php';
        $dbh = createPDO();
        try {
            $stmt = $dbh->prepare(SqlStatements::GET_ALL_QUESTIONS);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $h_sel1 = '<select id="s1" name="securityQuestion1" class="browser-default">';
            $h_sel2 = '<select id="s2" name="securityQuestion2" class="browser-default">';
            $h_sel3 = '<select id="s3" name="securityQuestion3" class="browser-default">';
            $html_select = '';
            foreach ($result as $key => $value) {
                $html_select .= "<option value=\"$key\">".$value['sque_question'].'</option>';
            }
            $html_select .= '</select>';
            echo '<div class="container">
               <div class="row"><div class="col s12 l8 offset-l2">
                 '.$h_sel1.$html_select.'
               </div></div>
               <div class="row">
                <div class="col s6 l4 offset-l2"><input id="s1i" name="s1i" type="password"></div>
                <div class="col s6 l4"><input id="s1ic" name="s1ic" type="password"></div>
               </div>
               <div class="row"><div class="col s12 l8 offset-l2">
                 '.$h_sel2.$html_select.'
               </div></div>
               <div class="row">
                <div class="col s6 l4 offset-l2"><input id="s2i" name="s2i" type="password"></div>
                <div class="col s6 l4"><input id="s2ic" name="s2ic" type="password"></div>
               </div>
               <div class="row"><div class="col s12 l8 offset-l2">
                 '.$h_sel3.$html_select.'
               </div></div>
               <div class="row">
                <div class="col s6 l4 offset-l2"><input id="s3i" name="s3i" type="password"></div>
                <div class="col s6 l4"><input id="s3ic" name="s3ic" type="password"></div>
               </div>
               <div class="row">
                <div class="col s12 l8 offset-l2"><div class="center">
                  <a href="#" id="submit" class="btn cyan lighten-1">Save Answers <i class="mdi-content-save right"></i></a>
                </div></div>
               </div>
              </div>';
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
    ?>
</main>
<footer>
    <div class="container"></div>
    <div class="footer-copyright">
        <div class="container">
            2014 - 2015 XVicarious Software Solutions
        </div>
    </div>
</footer>
</body>
</html>
