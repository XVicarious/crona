<?php
session_start();
if ($_SESSION["lastAction"] + 10 * 60 < time()) {
    session_destroy();
    echo '<script>window.location.replace("http://xvss.net/devel/time?timeout=1");</script>';
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
    <link rel="stylesheet" href="../css/materialize.css">
    <link rel="stylesheet" href="../css/material-extra.css">
    <link rel="stylesheet" href="../css/xvss-logo.css">
    <link rel="stylesheet" href="../css/timecard.css">
    <link rel="stylesheet" href="../css/cellCases.css">
    <link rel="stylesheet" href="../css/sticky-footer.css">
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
    <script src="../js/admin/initialLoad.js"></script>
    <script>
        $(function() {
            $('.button-collapse').sideNav();
        });
    </script>
</head>
<body>
<header>
    <nav class="top-nav grey">
        <div class="nav-wrapper">
            <div class="container">
                <div class="nav-wrapper">
                    <a class="page-title">Crona Timestamp</a>
                </div>
            </div>
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
    <div class="hide-on-large-only">
        <a href="#" data-activates="nav-mobile" class="button-collapse top-nav">
            <i class="mdi-navigation-menu"></i>
        </a>
    </div>
    <ul id="nav-mobile" class="side-nav fixed">
        <li class="logo attention">
            <a id="logo-container"><i class="attention-image icon-logo center"></i></a>
        </li>
        <li class="bold">
            <a href="#" id="view-employees" class="waves-effect waves-light">Manage Timecards</a>
        </li>
        <li class="bold">
            <a href="#" id="manage-schedules" class="waves-effect waves-light">Manage Schedules</a>
        </li>
        <li class="bold">
            <a href="#" id="add-employees" class="waves-effect waves-light">Add Employees</a>
        </li>
        <li class="bold">
            <a href="#" id="system-admin" class="waves-effect waves-light">System Administration</a>
        </li>
        <li class="bold">
            <a href="#export-times" id="export-times-button" class="waves-effect waves-light modal-trigger">
                Export CSV
            </a>
        </li>
    </ul>
</header>
<main>
    <div>
        <div class="row">
            <div class="col s12">
                <div>
                    <div id="ajaxDiv"></div>
                </div>
            </div>
        </div>
    </div>
</main>
<footer class="page-footer grey">
    <div class="footer-copyright">
        <div class="container">
            2015 XVicarious Software Solutions
        </div>
    </div>
</footer>
<div id="dialog-timecard" class="ui-helper-hidden" title="Edit Timecard">
    <div id="timecardDiv"></div>
</div>
<div id="dialog" class="modal modal-fixed-footer">
    <div class="modal-content">
        <h4 class="modal-title"></h4>
        <p class="modal-text"></p>
    </div>
    <div class="modal-footer"></div>
</div>
<div id="export-times" class="modal">
    <div class="modal-content">
        <h4 class="modal-title">Export CSV For Payroll</h4>
        <p id="exportC" class="modal-text"></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-export">Export CSV</a>
        <a href="#" class="waves-effect waves-light btn-flat modal-action modal-close modal-cancel">Cancel</a>
    </div>
</div>
</body>
</html>
