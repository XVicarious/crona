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
		<link rel="stylesheet" href="../css/jquery-ui.min.css">
		<link rel="stylesheet" href="../css/backgrid.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.7.0/underscore.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.2/backbone.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/backgrid.js/0.3.5/backgrid.min.js"></script>
        <script src="../js/lib/backbone.paginator.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/lunr.js/0.5.7/lunr.min.js"></script>
		<script src="../js/lib/backgrid-extension.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-contextmenu/1.6.5/jquery.contextMenu.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
		<script src="../js/lib/spin.min.js"></script>
		<script src="../js/lib/jquery.spin.js"></script>
		<script src="../js/admin/functions.async.js"></script>
        <script src="../js/admin/employee_list.js"></script>
		<script src="../js/admin/initialLoad.js"></script>
    </head>
    <body class="tc">
	<div id="head"><h2 style="padding:0;margin:0;text-align:center">Crona Timestamp</h2><h3 style="padding:0;margin:0;text-align:center">Administrative Console</h3></div>
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
					<a id="scheduleButton" class="sideNavigationCell" href="#schedulePeople">Schedules</a>
				</div>
				<div class="row">
					<a id="addemployeeButton" class="sideNavigationCell" href="#addEmployee">Add Employees</a>
				</div>
				<div class="row">
					<a class="sideNavigationCell">System Management</a>
				</div>
				<div class="row">
					<span class="sideNavigationCell">Export<form><div id="exportC"></div><br><input id="exportcsv" type=button value="Export CSV"></form><div id="exportScript"></div></span>
				</div>
			</div>
		</div>
		<div id="ajaxDiv"></div>
	</div>
	<div id="dialog-confirm" class="ui-helper-hidden" title="Delete Timestamp?"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This will delete the timestamp forever!  Are you sure you want to do this?</p></div>
	<div id="dialog-timecard" class="ui-helper-hidden" title="Edit Timecard"><p><div id="timecardDiv"></div></p></div>
	</body>
</html>
