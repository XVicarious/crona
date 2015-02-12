<?
function validateLogin($pass, $hashedPass, $salt, $hashMethod = 'sha1') {
	if (function_exists('hash') && in_array($hashMethod, hash_algos())) {
		return ($hashedPass === hash($hashMethod, $salt . $pass));
	}
    return ($hashedPass === sha1($salt . $pass));
}
if ($_POST) {
	$conn = mysqli_connect("localhost","bmaurer_pciven","3al12of4ut25","bmaurer_hhemployee");
	$myu = $_POST['uname'];
	$query = "SELECT user_id, user_name, user_password, user_password_set, user_emails FROM employee_list WHERE employee_list.user_name = \"$myu\"";
	$result = mysqli_query($conn, $query);
	if ($result !== false) {
		if (mysqli_num_rows($result) !== 0) {
			list($uid, $uname, $upas, $udate, $uemail) = mysqli_fetch_row($result);
			$salt = substr($upas,0,8);
			if (validateLogin($_POST['drowp'], substr($upas, 8), $salt)) {
				$passwordSetLapse = time() - strtotime($udate);
				if ($passwordSetLapse >= 15742080) {
					/*$url = 'http://xvss.net/time/resetutil.php';
					$data = array('email'=>$uemail,'function'=>'sendEmail');
					$options = array('http'=>array(
						'header'=>'Content-type: application/x-www-form-urlencoded\r\n',
						'method'=>'POST',
						'content'=>http_build_query($data)
					));
					$context = stream_context_create($options);
					file_get_contents($url,false,$context);*/
					//http_post_fields('http://xvss.net/time/resetutil.php',['email'=>$uemail,'function'=>'sendEmail']);
					$data = http_build_query(['function'=>'sendEmail','email'=>$uemail]);
					$opts = array('http'=>array('method'=>'POST','content'=>$data));
					$st = stream_context_create($opts);
					$fp = fopen('http://xvss.net/time/resetutil.php','rb',false,$st);
					echo 'Password Expired!  Reset link set to your email.';
					return;
				}
				$query = 'SELECT sec_1,sec_2,sec_3 FROM employee_security WHERE sec_user_id = '.$uid;
				$result = mysqli_query($conn, $query);
				if (mysqli_num_rows($result) !== 0) {
					list($s1,$s2,$s3) = mysqli_fetch_row($result);
					if ($s1 === "" || $s2 === "" || $s3 === "") {
						session_start();
						$_SESSION["lastAction"] = time();
						$_SESSION["userId"] = $uid;
						echo '<script>$(location).attr("href","http://xvss.net/time/set_security_questions.php?s=partial")</script>';
						return;
					}
				} else {
					session_start();
					$_SESSION["lastAction"] = time();
					$_SESSION["userId"] = $uid;
					echo '<script>$(location).attr("href","http://xvss.net/time/set_security_questions.php")</script>';
					return;
				}
				if ($_POST["loginType"] === "timestamp") {
						if ($_SERVER['REMOTE_ADDR'] === '40.132.64.225') {
							date_default_timezone_set('Atlantic/Reykjavik');
							$now = date("Y-m-d H:i:s");
							$iquery = "INSERT INTO timestamp_list (user_id_stamp,timestamp_list.datetime) VALUES ($uid,'$now')";
							mysqli_query($conn, $iquery);
							echo "Timestamp Accepted!";
						} else {
							echo "Timestamp <b>NOT</b> Accepted!";
						}
				} else if ($_POST["loginType"] === "cardAdmin") {
					session_start();
					$_SESSION["lastAction"] = time();
					$_SESSION["userId"] = $uid;
					echo "<div id=\"a\"></div>";
				} else {
					setcookie("xvtss","$uid",time()+1200);
					echo "<div id=\"b\"></div>";
				}
			} else {
				echo "Bad Username or Password!";
				echo "<div id=\"badup\"></div>";
			}
		} else {
			echo "Bad Username or Password!";
			echo "<div id=\"badup\"></div>";
		}
	} else {
		echo 'SEVERE ERROR: PLEASE REPORT TO ADMINISTRATOR';
		echo "<div id=\"badup\"></div>";
	}
	mysqli_close($conn);
}