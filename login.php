<!DOCTYPE HTML>
<?PHP
	session_start();
	require 'functions.php';
	$fingerprint = fingerprint();
	connect();
	
	if(isset($_POST['login'])){
		
		// Include passwort pepper
		require 'config/pepper.php';
		
		// Sanitize user input
		$log_user = sanitize($_POST['log_user']);
		$log_pw = sanitize($_POST['log_pw']);
		
		// Select user details from USER
		$sql_log = "SELECT * FROM user, ugroup WHERE user.ugroup_id = ugroup.ugroup_id AND user_name = '$log_user'";
		$query_log = mysql_query($sql_log);
		check_sql($query_log);
		$result_log = mysql_fetch_assoc($query_log);
		
		// Verify Password
		if(password_verify($log_pw.$pepper, $result_log['user_pw'])){
			
			// Define Session Variables for this User
			$_SESSION['log_user'] = $log_user;
			$_SESSION['log_time'] = time();
			$_SESSION['log_id'] = $result_log['user_id'];
			$_SESSION['log_ugroup'] = $result_log['ugroup_name'];
			$_SESSION['log_admin'] = $result_log['ugroup_admin'];
			$_SESSION['log_delete'] = $result_log['ugroup_delete'];
			$_SESSION['log_report'] = $result_log['ugroup_report'];
			$_SESSION['log_fingerprint'] = $fingerprint;
			
			// Check if user logged out properly last time
			$sql_logout = "SELECT logrec_id, logrec_logout FROM logrec WHERE logrec_id IN (SELECT MAX(logrec_id) FROM logrec WHERE user_id = '$_SESSION[log_id]')";
			$query_logout = mysql_query($sql_logout);
			check_sql($query_logout);
			$logout = mysql_fetch_array($query_logout);
			$_SESSION['logrec_logout'] = $logout[1];
			
			// Close all open sessions for that user
			$sql_close_logrec = "UPDATE logrec SET logrec_end = '$_SESSION[log_time]' WHERE user_id = '$_SESSION[log_id]' AND logrec_end IS NULL";
			$query_close_logrec = mysql_query($sql_close_logrec);
			check_sql($query_close_logrec);
			
			// Record Login in LOGREC
			$sql_logrec = "INSERT INTO logrec (user_id, logrec_start, logrec_logout) VALUES ('$_SESSION[log_id]', '$_SESSION[log_time]', '0')";
			$query_logrec = mysql_query($sql_logrec);
			check_sql($query_logrec);
			
			// Find LOGREC_ID for current user
			$sql_logrecid = "SELECT MAX(logrec_id) FROM logrec WHERE user_id = '$_SESSION[log_id]'";
			$query_logrecid = mysql_query($sql_logrecid);
			check_sql ($query_logrecid);
			$logrecid = mysql_fetch_array($query_logrecid);
			$_SESSION['logrec_id'] = $logrecid['MAX(logrec_id)'];
			
			// Forward to start.php
			header('Location: start.php');
		}
		else error('Authentification failed!\nWrong Username and/or Password!');
	}
?>
<html>
	<?PHP include_Head('Microfinance Management') ?>	
	
	<body>
		<div class="content_center" style="width:100%; margin-top:15em">
		
		<!-- LEFT SIDE: mangoO Logo -->
		<div class="content_left" style="width:50%; padding-right:5em; text-align:right;">
			<img src="ico/mangoo_l.png" style="width:75%;">
		</div>
		
		<!-- RIGHT SIDE: Login Form -->
		<div class="content_right" style="width:50%; padding-left:5em; text-align:left;">
			
			<p class="heading" style="margin:0; text-align:left;">Please login...</p>
			
			<form action="login.php" method="post">
				<table id="tb_fields" style="margin:0; border-spacing:0em 1.25em;">
					<tr>
						<td>
							<input type="text" name="log_user"  placeholder="Username" />
						</td>
					</tr>
					<tr>
						<td>
							<input type="password" name="log_pw" placeholder="Password" />
						</td>
					</tr>
					<tr>
						<td>
							<input type="submit" name="login" value="Login">
						</td>
					</tr>
				</table>
			</form>
			
		</div>
		
	</body>
</html>