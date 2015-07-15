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
    <link rel="stylesheet" href="../css/materialize.css?v=0.97">
    <link rel="stylesheet" href="../css/material-extra.css">
    <link rel="stylesheet" href="../css/xvss-logo.css">
    <link rel="stylesheet" href="../css/timecard.css">
    <link rel="stylesheet" href="../css/cellCases.css">
    <link rel="stylesheet" href="../css/sticky-footer.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/materialize.js?v=0.97"></script>
    <script src="../js/lib/underscore.js"></script>
    <script src="../js/lib/backbone.js"></script>
    <script src="../js/lib/backgrid.js"></script>
    <script src="../js/lib/backbone.paginator.js"></script>
    <!--<script src="../js/lib/backgrid-filter.js"></script>-->
    <script src="../js/lib/jquery-ui.js"></script>
    <script src="../js/lib/jquery.contextMenu.js"></script>
    <script src="../js/lib/moment.min.js"></script>
    <script src="../js/lib/backgrid-moment-cell.js"></script>
    <script src="../js/timeConstants.js"></script>
    <script src="../js/lib/jquery.pjax.js"></script>
    <script src="../js/admin/functions.async.js"></script>
    <script src="../js/admin/initialLoad.js"></script>
    <script src="../js/admin/adminConsole.js"></script>
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
    </nav>
    <div class="hide-on-large-only">
        <a href="#" data-activates="nav-mobile" class="button-collapse top-nav">
            <i class="mdi-navigation-menu"></i>
        </a>
    </div>
    <ul id="nav-mobile" class="side-nav fixed">
        <!--<li class="logo attention">
            <a id="logo-container"><i class="attention-image icon-logo center"></i></a>
        </li>-->
        <li class="user-details orange">
            <div class="row">
                <div class="col s4 m4 l4">
                    <img alt class="circle responsive-img valign profile-image">
                </div>
                <div class="col s8 m8 l8">
                    <a class="btn-flat dropdown-button white-text profile-button" href="#" data-activates="profile-dropdown">
                        Username
                        <i class="mdi-navigation-arrow-drop-down right"></i>
                    </a>
                    <ul id="profile-dropdown" class="dropdown-content active">
                        <li>
                            <a href="#">
                                <i class="mdi-hardware-keyboard-tab"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                    <p class="user-role">Administrator</p>
                </div>
            </div>
        </li>
        <li class="bold">
            <a href="#" id="view-employees" class="waves-effect waves-light">Manage Timecards</a>
        </li>
        <li class="bold">
            <a href="#" id="manage-schedules" class="waves-effect waves-light">Manage Schedules</a>
        </li>
        <li class="bold">
            <a id="add-employees" class="grey-text lighten-4 waves-effect waves-light">Add Employees</a>
        </li>
        <li class="bold">
            <a href="#" id="system-admin" class="waves-effect waves-light">System Administration</a>
        </li>
        <li class="bold">
            <a href="#export-times" id="export-times-button" class="waves-effect waves-light modal-trigger">
                Export CSV
            </a>
        </li>
        <li class="bold">
            <a href="#about-log" id="about-log-button" class="waves-effect waves-light modal-trigger">
                About Crona
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
<div id="dialog" class="modal">
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
<div id="about-log" class="modal">
    <div class="modal-content">
        <h4 class="modal-title">About Crona</h4>
        <p class="modal-text">
            Crona was written by Brian Maurer.  It uses technologies such as Javascript and PHP to create a quick and flexible timecard management system.
            Crona uses libraries such as <a href="https://jquery.com/">jQuery</a>, <a href="https://github.com/jashkenas/underscore">Underscore</a>, <a href="https://github.com/jashkenas/backbone/">Backbone</a>, and <a href="https://github.com/wyuenho/backgrid">Backgrid</a> to acomplish this.
            <a href="https://github.com/Dogfalo/materialize">Materialize</a> was used for the UI of Crona, and to make it mobile responsive.
            <br /><br />
            If you have any questions, concerns, complaints, or bug reports please email Brian Maurer at bmaurer@harthotels.com
            <hr />
            <ul class="collection with-header">
                <li class="collection-header"><h6>Known Bugs</h6></li>
                <li class="yellow lighten-4 collection-item">If the menu is opened while Crona's window is small, when the window is made bigger the menu disappears until you resize it again.</li>
                <li class="yellow lighten-4 collection-item">When Crona's window is small (or on mobile), the menu will not close when you choose an option.</li>
                <li class="yellow lighten-4 collection-item">When adding days after Monday in a timecard, you can add past the selected period. For example, after adding Tuesday, you will be able to add NEXT Sunday.</li>
                <li class="yellow lighten-4 collection-item">Times are not perfectly centered in their boxes.</li>
            </ul>
            <ul class="collection with-header">
                <li class="collection-header"><h6>Features In Development</h6></li>
                <li class="green lighten-4 collection-item">Designate overnight shifts.</li>
                <li class="green lighten-4 collection-item">Create schedules for employees.</li>
                <li class="green lighten-4 collection-item">Add Employees</li>
                <li class="green lighten-4 collection-item">Configurable timezones</li>
                <li class="green lighten-4 collection-item">Configurable password requirements.</li>
                <li class="green lighten-4 collection-item">AUTOMAGIC payable benefits!</li>
                <li class="green lighten-4 collection-item">Drag and drop timestamps!</li>
            </ul>
        </p>
    </div>
</div>
</body>
</html>
