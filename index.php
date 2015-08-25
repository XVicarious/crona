<html>
<head>
    <meta name="theme-color" content="#ff9800">
    <title>Crona Timestamp Login</title>
    <link rel="shortcut icon" href="favicon.ico"/>
    <link rel="stylesheet" href="css/materialize.css">
    <link rel="stylesheet" href="css/material-extra.css">
    <link rel="stylesheet" href="css/sticky-footer.css">
    <script src="js/lib/jquery.js"></script>
    <script src="js/lib/materialize.js"></script>
    <script src="js/timeSubmit.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
</head>
<body>
<header class="login">
    <nav class="green darken-3">
        <div class="nav-wrapper">
            <a class="page-title">Crona Timestamp</a>
        </div>
    </nav>
</header>
<main class="login green lighten-5">
    <div class="container">
        <div class="row">
            <div class="col s12">
                <div id="ajaxDiv"></div>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l6 offset-l3">
                <i class="mdi-action-account-circle prefix blue-text lighten-1"></i>
                <input id="uname" type="text" placeholder="Username" class="validate">
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12 l6 offset-l3">
                <i class="mdi-communication-vpn-key prefix blue-text lighten-1"></i>
                <input id="drowp" type="password" placeholder="Password" class="validate">
            </div>
        </div>
        <div class="row">
            <div class="col s12 l6 offset-l3">
                <label for="loginType">Login Type</label>
                <select class="browser-default" id="loginType">
                    <option value="timestamp" selected>Timestamp</option>
                    <option value="viewCards">View Card</option>
                    <option value="cardAdmin">Administer</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col s12 l6 offset-l3">
                <div class="center">
                    <a class="blue lighten-1 waves-effect waves-light btn-large" id="submit-button">Sign in<i class="mdi-action-lock-open right"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col s12 l6 offset-l3">
                <div class="center">
                    Forgot your password? Click <a href="forgot">here</a>.
                </div>
            </div>
        </div>
    </div>
</main>
<footer class="login page-footer green darken-3">
    <div class="container"></div>
    <div class="footer-copyright">
        <div class="container">
            2015 XVicarious Software Solutions
        </div>
    </div>
</footer>
<div id="bad-login" class="modal">
    <div class="modal-content">
        <h4>Bad Login!</h4>
        <p class="modal-message"></p>
    </div>
    <div class="modal-footer">
        <a href="#" class="waves-effect waves-orange btn-flat modal-action modal-close">Okay</a>
    </div>
</div>
</body>
</html>
