<?php 
/* 
 * Copyright (C) 2009 Huan Truong
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY AUTHOR AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL AUTHOR OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

include_once ("includes/config.php");
include_once ("includes/utils.php");
include_once ("includes/headfoot.php");

if ($_GET['act'] == '') {
	echo _header('User/Administrator Login');
	?>
		<div id="yui-main">
			<h1>Login</h1>
			<?php if ($_GET['wrongPassword']=='true') echo "<p>You have entered a wrong password, please try again.</p>" ?>
			<p>Please enter your username and password.</p>
			<form method="post" action="?act=login">
				<table align="center">
					<tr>
						<td>Username:</td>
						<td><input id="userName" name="userName" value="root" /></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" id="password" name="password" /></td>
					</tr>
					<tr>
						<td colspan="2" align="center"><input type="submit" /></td>
					</tr>
				<table>
			</form>
		</div>
	<?php
	echo _footer();

// Create new user, after submiting the form
} elseif ($_GET['act'] == 'login') {	
	// Now set the loginSuccessful flag to false
	$loginSuccessful = false;
	
	$dbControlHandler = new SQLiteDatabase(DB_CONTROL_FILE);
	
	$username = escape_string($_POST['userName']);
	$password = $_POST['password']; 
	// We do not have to escape the password, because this will be hashed anyway.
	
	$loginQuery = $dbControlHandler->query(
		"SELECT * FROM users WHERE username = \"$username\";"
	);
	
	// Now check if there is at least a user that matches the
	// entered data.
	while($loginQuery->valid()) {
		$entry = $loginQuery->current();
		// Calculate Password Hash
		$thisUserSalt = $entry['salt'];
		$thisUserPwHash = $entry['password'];
		$calculatedHash = md5($password . $thisUserSalt);
		if ($calculatedHash == $thisUserPwHash) {
			// Raise the loginSuccesful Flag
			// and set some session variables.
			$loginSuccessful = true;
			session_start();
			$_SESSION['loggedinUsername'] = $username;
			$_SESSION['loggedinUserPerms'] = $entry['permissions'];
		}
		$loginQuery->next();
	}
	
	if (!$loginSuccessful) {
		header("location: login.php?wrongPassword=true");
	}
		
	echo _header(_('Login...'));
	?>
		<div id="yui-main">
			<h1>Login Successful!</h1>
			<div>
				<p>Please <a href="./">click here</a> to get back to the homepage.</p>
			</div>
		</div>
	<?php
	echo _footer();
	
} else {
	die('Not Implemented!');
}
?>