<?php
session_start();
if ($_SESSION["lastAction"] + 10 * 60 < time()) {
    session_destroy();
    echo '<script>window.location.replace("http://xvss.net/time?timeout=1");</script>';
}
$_SESSION["lastAction"] = time();
?>
<html>
<head>
    <meta name="theme-color" content="#dd2c00">
    <title>Crona Timestamp</title>
    <link rel="shortcut icon" href="../favicon.ico"/>
    <link rel="stylesheet" href="../css/materialize.css">
    <link rel="stylesheet" href="../css/material-extra.css">
    <link rel="stylesheet" href="../css/timecard.css">
    <link rel="stylesheet" href="../css/cellCases.css">
    <link rel="stylesheet" href="../css/sticky-footer.css">
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/materialize.js"></script>
    <script src="../js/lib/underscore.js"></script>
    <script src="../js/lib/backbone.js"></script>
    <script src="../js/lib/backgrid.js"></script>
    <script src="../js/lib/backbone.paginator.js"></script>
    <script src="../js/lib/moment.min.js"></script>
    <script src="../js/lib/backgrid-moment-cell.js"></script>
    <script src="../js/timeConstants.js"></script>
    <script src="../js/view/viewTimecard.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>
<body class="tc">
<header class="login">
    <nav class="orange">
        <div class="nav-wrapper">
            <a class="page-title">Crona Timestamp</a>
            <ul id="nav-mobile" class="right">
                <!--<li><a href="#" onclick="document.cookie='xvtss=;expires=Wed 01 Jan 1970';window.location.replace('http://xvss.net/devel/time?timeout=1');">Log Out</a></li>-->
            </ul>
        </div>
    </nav>
</header>
<main class="login">
    <div class="container">
        <div class="row">
            <div class="col s12">
                <div id="ajaxDiv"></div>
            </div>
        </div>
    </div>
</main>
<footer class="login orange page-footer">
    <div class="container"></div>
    <div class="footer-copyright">
        <div class="container">
            2015 XVicarious Software Solutions
        </div>
    </div>
</footer>
</body>
</html>
