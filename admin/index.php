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
    <link rel="stylesheet" href="../css/style.css" type="text/css">
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="../css/jquery-ui.theme.css">
    <link rel="stylesheet" href="../css/jquery.contextMenu.css">
    <link rel="stylesheet" href="../css/backgrid.css">
    <link rel="stylesheet" href="../css/timecard.css">
    <link rel="stylesheet" href="../css/cellCases.css">
    <script src="../js/lib/jquery.js"></script>
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
</head>
<body class="tc">
<div id="head"><h2 style="padding:0;margin:0;text-align:center">Crona Timestamp</h2>

    <h3 style="padding:0;margin:0;text-align:center">Administrative Console</h3></div>
<div id="container">
    <div id="navigation">
        <div class="sideNavigationTable">
            <div class="row">
                <h3 class="sideNavigationCell">Options</h3>
            </div>
            <div class="row">
                <a id="timecardButton" class="sideNavigationCell" href="#viewTimecards">Timecards</a>
            </div>
            <div class="row">
                <a id="scheduleButton" class="sideNavigationCell" onclick="mode = 'schedule'">Schedules</a>
            </div>
            <div class="row">
                <a id="addemployeeButton" class="sideNavigationCell" href="#addEmployee">Add Employees</a>
            </div>
            <div class="row">
                <a class="sideNavigationCell">System Management</a>
            </div>
            <div class="row">
                <span class="sideNavigationCell">Export<form>
                        <div id="exportC"></div>
                        <br><input id="exportcsv" type=button value="Export CSV"></form><div
                        id="exportScript"></div></span>
            </div>
        </div>
    </div>
    <div id="ajaxDiv"></div>
</div>
<div id="dialog-confirm" class="ui-helper-hidden" title="Delete Timestamp?">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
        This will delete the timestamp forever! Are you sure you want to do this?
    </p>
</div>
<div id="dialog-timecard" class="ui-helper-hidden" title="Edit Timecard"><p>

    <div id="timecardDiv"></div>
    </p></div>
</body>
</html>
