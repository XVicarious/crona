<html>
<head>
    <meta name="theme-color" content="#dd2c00">
    <title>Crona Timestamp Login</title>
    <link rel="shortcut icon" href="favicon.ico"/>
    <link rel="stylesheet" href="css/materialize.css">
    <link rel="stylesheet" href="css/material-extra.css">
    <script src="js/lib/jquery.js"></script>
    <script src="js/lib/materialize.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>
<body>
<header class="login">
    <nav class="orange">
        <div class="nav-wrapper">
            <a class="page-title">Crona Timestamp</a>
        </div>
    </nav>
</header>
<main class="login orange lighten-5">
    <div class="container">
        <div class="row">
            <div class="col s12">
                <div id="ajaxDiv"></div>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12">
                <i class="mdi-action-account-circle prefix orange-text darken-1"></i>
                <input id="uname" type="text" class="validate">
                <label for="uname">Username</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12">
                <i class="mdi-communication-vpn-key prefix orange-text darken-1"></i>
                <input id="drowp" type="password" class="validate">
                <label for="drowp">Password</label>
            </div>
        </div>
        <div class="row">
            <div class="col s12">
                <label for="loginType">Login Type</label>
                <select class="browser-default" id="loginType">
                    <option value="timecard" selected>Timestamp</option>
                    <option value="viewCards">View Card</option>
                    <option value="cardAdmin">Administer</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col s12">
                <div class="center">
                    <a class="orange waves-effect waves-light btn">Submit</a>
                </div>
            </div>
        </div>
    </div>
</main>
<footer class="login page-footer orange">
    <div class="container"></div>
    <div class="footer-copyright">
        <div class="container">
            2015 XVicarious Software Solutions
        </div>
    </div>
</footer>
</body>
</html>
