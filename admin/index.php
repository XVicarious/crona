<?php
require 'admin_functions.php';
sessionCheck();
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="shortcut icon" href="https://xvicario.us/static/images/favicon.ico"/>
    <link rel="stylesheet" href="https://xvicario.us/static/css/jquery.ui.contextMenu.min.css">
    <link rel="stylesheet" href="https://xvicario.us/static/css/materialize.min.css">
    <link rel="stylesheet" href="https://xvicario.us/static/css/common.css">
    <link rel="stylesheet" href="https://xvicario.us/static/css/admin.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
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
    <ul id="nav-mobile" class="side-nav fixed" style="overflow: hidden">
        <!--<li class="logo attention">
            <a id="logo-container"><i class="attention-image icon-logo center"></i></a>
        </li>-->
        <li class="user-details orange">
            <div class="row">
                <div class="col s4 m4 l4">
                    <img alt class="circle responsive-img valign profile-image" src="https://xvicario.us/static/images/generic_profile_image.png">
                </div>
                <div class="col s8 m8 l8">
                    <a class="btn-flat dropdown-button white-text profile-button" href="#"
                       data-activates="profile-dropdown">
                        Username
                        <i class="mdi-navigation-arrow-drop-down right"></i>
                    </a>
                    <ul id="profile-dropdown" class="dropdown-content active">
                        <li>
                            <a id="logout-button" href="logout.php">
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
            <a href="#" id="view-employees" class="waves-effect waves-light">Manage Timecards
                <span class="badge">0</span>
            </a>
        </li>
        <!--<li class="bold">
            <a href="#" id="manage-schedules" class="waves-effect waves-light">Manage Schedules</a>
        </li>-->
        <!--<li class="bold">
            <a href="#" id="add-employees" class="waves-effect waves-light">Add Employees</a>
        </li>-->
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
                <div class="row">
                    <div id="ajaxDiv">
                        <div class="preloader-wrapper big active center">
                            <div class="spinner-layer spinner-blue-only">
                                <div class="circle-clipper left">
                                    <div class="circle"></div>
                                </div>
                                <div class="gap-patch">
                                    <div class="circle"></div>
                                </div>
                                <div class="circle-clipper right">
                                    <div class="circle"></div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <div class="modal-export"></div>
</div>
<div id="about-log" class="modal">
    <div class="modal-content">
        <h4 class="modal-title">About Crona v0.3</h4>
        <p class="modal-text">
            Crona was written by Brian Maurer.  It uses technologies such as Javascript and PHP to create a quick and
            flexible timecard management system.
            Crona uses libraries such as <a href="https://jquery.com/">jQuery</a> to acomplish this.
            <a href="https://github.com/Dogfalo/materialize">Materialize</a> was used for the UI of Crona, and to make
            it mobile responsive.
            <br /><br />
            If you have any questions, concerns, complaints, or bug reports please email Brian Maurer at
            bmaurer@harthotels.com
            <hr />
            <ul class="collection with-header">
                <li class="collection-header"><h6>Known Bugs</h6></li>
                <li class="collection-header">All of these are currently being investigated.  They will be fixed as soon
                    as possible.</li>
                <li class="yellow lighten-4 collection-item">If the menu is opened while Crona's window is small, when
                    the window is made bigger the menu disappears until you resize it again.</li>
                <li class="yellow lighten-4 collection-item">When Crona's window is small (or on mobile), the menu will
                    not close when you choose an option.</li>
                <li class="yellow lighten-4 collection-item">Logout button overlays the username</li>
                <li class="yellow lighten-4 collection-item">If you are timed out, clicking "logout" will not bring
                    you to the signin screen.</li>
                <li class="yellow lighten-4 collection-item">"Specific Date" and "Specific Period" are offset by 1.  To
                    get the correct range you want: for Specific Date, click on the day before the one you want.  For
                    Specific Period, choose the date before on both selections.</li>
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
<script src="/devel/time/js/lib/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/history.js/1.8/bundled-uncompressed/html4+html5/jquery.history.js"></script>
<script src="/devel/time/js/lib/materialize.js?v=0.97"></script>
<script src="/devel/time/js/lib/jquery-ui.js"></script>
<script src="/devel/time/js/lib/jquery.contextMenu.js"></script>
<script src="/devel/time/js/lib/moment.min.js"></script>
<script src="/devel/time/js/timeConstants.js"></script>
<script src="/devel/time/js/modes.js"></script>
<script src="/devel/time/js/lib/jquery.pjax.js"></script>
<script src="/devel/time/js/testing/combinedSource.js"></script>
<script src="/devel/time/js/admin/adminConsole.js"></script>
</body>
</html>
