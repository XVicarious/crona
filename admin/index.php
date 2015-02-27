<?php
session_start();
if ($_SESSION["lastAction"] + 10 * 60 < time()) {
    session_destroy();
    echo '<script>$(location).attr("href","http://xvss.net/time?timeout=1")</script>';
}
$_SESSION["lastAction"] = time();
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="shortcut icon" href="../favicon.ico"/>
    <!--<link rel="stylesheet" href="../css/style.css" type="text/css">-->
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/jquery-ui.theme.css">
    <link rel="stylesheet" href="../css/jquery.contextMenu.css">
    <link rel="stylesheet" href="../css/backgrid.css">
    <link rel="stylesheet" href="../css/timecard.css">
    <link rel="stylesheet" href="../css/cellCases.css">
    <link rel="stylesheet" href="../css/materialize.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/materialize.js"></script>
    <script src="../js/lib/underscore.js"></script>
    <script src="../js/lib/backbone.js"></script>
    <script src="../js/lib/backgrid.js"></script>
    <script src="../js/lib/backbone.paginator.js"></script>
    <!--<script src="../js/lib/backgrid-filter.js"></script>-->
    <script src="../js/lib/jquery-ui.js"></script>
    <script src="../js/lib/jquery.contextMenu.js"></script>
    <script src="../js/lib/moment.js"></script>
    <script src="../js/lib/backgrid-moment-cell.js"></script>
    <script src="../js/lib/spin.js"></script>
    <script src="../js/lib/jquery.spin.js"></script>
    <script src="../js/admin/functions.async.js"></script>
    <script src="../js/admin/employee_list.js"></script>
    <script src="../js/admin/initialLoad.js"></script>
    <script>
        $(function() {
            $('.button-collapse').sideNav();
        });
    </script>
</head>
<body>
<div>
    <nav class="grey">
        <div class="nav-wrapper">
            <a href="#" class="brand-logo center">Crona Timestamp</a>
            <ul id="slide-out" class="side-nav">
                <li><a href="#!" id="timecardButton">Timecards</a></li>
                <li><a href="#!" id="scheduleButton">Schedules</a></li>
            </ul>
            <a href="#" data-activates="slide-out" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
        </div>
        <!--<div class="col s12 m4 l3 grey lighten-2">
            <h3 class="row" >Options</h3>
            <a class="row" id="timecardButton">Timecards</a>
            <a class="row" id="scheduleButton">Schedules</a>
            <a class="row" id="addemployeeButton" href="#addEmployee">Add Employees</a>
            <a class="row" >System Management</a>
        <span class="row" >
            Export
            <form>
                <div id="exportC"></div><br><input id="exportcsv" type=button value="Export CSV">
            </form>
            <div id="exportScript"></div>
        </span>
        </div>-->
    </nav>
    <div class="row">
        <div class="col l2 hide-on-med-and-down grey lighten-2">
            <!-- This all becomes a 'mode'.  No longer consider separate 'tabs' -->
            <form>
            <p>
                <input name="editWhat" type="radio" id="timecardButton" checked/>
                <label for="timecardButton">Timecards</label>
            </p>
            <p>
                <input name="editWhat" type="radio" id="scheduleButton"/>
                <label for="scheduleButton">Schedules</label>
            </p>
            </form>
        </div>
        <div class="col s12 m12 l10 grey lighten-3">
            <div id="ajaxDiv"></div>
        </div>
    </div>
</div>
<footer class="page-footer grey">
    <div class="footer-copyright">
        <div class="container">
            2015 XVicarious Software Solutions
        </div>
    </div>
</footer>
<div id="dialog-timecard" class="ui-helper-hidden" title="Edit Timecard">
    <p><div id="timecardDiv"></div></p>
</div>
<div id="dialog" class="modal">
    <div class="modal-content">
        <h4 id="modal-title"></h4>
        <p id="modal-text"></p>
    </div>
    <div id="modal-footer" class="modal-footer"></div>
</div>
</body>
</html>
