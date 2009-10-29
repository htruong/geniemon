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
 
 ////////////////////////////////////////////////////////////////////////////
 
 // Setup wizard
 
include_once ("includes/config.php");
include_once ("includes/utils.php");
include_once ("includes/headfoot.php");
 
// Check if the control database exists.
//$isDbInitialized = file_exists(DB_CONTROL_FILE);
//if ($isDbInitialized) die('Genie Monitor Suite is already set-up!');

if ($_GET['act'] == '') {
	echo _header('New Administrator User Setup');
	?>
		<div id="yui-main">
			<h1>New Root User Creation</h1>
			<p>Thank you for choosing Genie Monitor Suite as the product to maintain your computer lab. Please take a few minutes setting up Genie.</p>
			<p>Please create the first user to access to Genie's functionalities. This user will have highest permission and will be allowed to do anything.</p>
			<form method="post" action="?act=createNewUser">
				<table align="center">
					<tr>
						<td>Username:</td>
						<td><input id="userName" name="userName" value="root" /></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" id="password" name="password" />
							<br />Please beware, Genie won't ask you to confirm your password.</td>
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
} elseif ($_GET['act'] == 'createNewUser') {	
	$dbControlHandler = new SQLiteDatabase(DB_CONTROL_FILE);
	
	// Create Users Table
	$dbControlHandler->query(
		"CREATE TABLE users(id INTEGER PRIMARY KEY, username CHAR(60), salt CHAR(10), password CHAR(32), permissions INTEGER);"
	);
	
	$userName = $_POST['userName'];
	$userPw = $_POST['password'];
	
	addNewUser ($dbControlHandler, $userName, $userPw, GLOBAL_ADMIN);
	
	$dbTrackHandler = new SQLiteDatabase(DB_TRACK_FILE);
	// Create Tracking Table
	$dbTrackHandler->query(
		"BEGIN;
		CREATE TABLE computers(id INTEGER PRIMARY KEY, name CHAR(250), region INTEGER, x INTEGER, y INTEGER, comment INTEGER, icon CHAR(150), laststatus INTEGER, lastsignal INTEGER);
		CREATE TABLE miscrecords(id INTEGER PRIMARY KEY, name CHAR(250), timestamp INTEGER, recordtype INTEGER, data TEXT);
		CREATE TABLE trackrecords(id INTEGER PRIMARY KEY, name CHAR(250), time INTEGER, status INTEGER);
CREATE TABLE zones(id INTEGER PRIMARY KEY, region_name CHAR(255), region_width INTEGER, region_height INTEGER, region_map_img CHAR(255));
		COMMIT;"
	);	
	echo _header(_('New Database Setup'));
	?>
		<div id="yui-main">
			<h1>Database Creation Completed!</h1>
			<div>
				<p>The Database Creation Process is completed. Please login to your root account!</p>
			</div>
		</div>
	<?php
	echo _footer();
	
} else {
	die('Not Implemented!');
}

 
 
 
?>