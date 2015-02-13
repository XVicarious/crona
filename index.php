<html>
    <head>
        <title>Crona Timestamp Login</title>
        <link rel="shortcut icon" href="favicon.ico"/>
        <meta name=viewport content="width=device-width, initial-scale=1">
    </head>
    <body>
		<div id="timeclock"></div>
        <div id="bestdiv">
			<div id="ajaxDiv"></div>
            <form id="formy" class="ui-widget">
                <table id="loginForm">
                    <tr><td rowspan="2" style="width:64px"><image width="87px" height="64px" src="crona.png"></image></td><td><input style="width:100%" placeholder="Username" class="ui-corner-all" type="text" name="uname" id="uname" /></td></tr>
                    <tr><td><input style="width:100%" class="ui-corner-all" placeholder="Password" type="password" name="drowp" id="drowp" /></td></tr>
					<tr><td colspan="2">
                            <div id="typeRadio">
                                <input type=radio name="loginType" value="timestamp" id="makeTimestamp" checked><label for="makeTimestamp">Timestamp</label>
                                <input type=radio name="loginType" value="viewCards" id="viewTimestamp"><label for="viewTimestamp">View Card</label>
                                <input type=radio name="loginType" value="cardAdmin" id="admnTimestamp"><label for="admnTimestamp">Administer</label>
                            </div>
                    </td></tr>
                    <tr><th colspan="2"><input id="submitButton" type="button" value="Submit" /></th></tr>
                </table>
                <p id="copy">written by: Brian Maurer and XVicarious Software Solutions</p>
            </form>
        </div>
        <link rel="stylesheet" href="css/login.css"/>
        <link rel="stylesheet" href="css/jquery-ui.css" />
        <link rel="stylesheet" href="css/jquery-ui.theme.css">
        <script defer src="js/lib/jquery.js"></script>
        <script defer src="js/lib/jquery-ui.js"></script>
        <script defer src="./js/timeSubmit.js"></script>
    </body>
</html>