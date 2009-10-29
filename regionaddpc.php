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

if($_SERVER["REQUEST_METHOD"] != "POST") {
	die('Invalid request.');
}

initSession();

if (!($_SESSION['loggedinUserPerms'] & EDIT_ZONES)) {
	die('You do not have permission to view this page!');
}

$dbTrackHandler = new SQLiteDatabase(DB_TRACK_FILE);

$regid = intval($_POST['region_id']);
$compid = intval($_POST['id']); 
if ($compid == 0) {
	$dbTrackHandler->query(
		'INSERT INTO computers '.
		'(name,x,y,region) '.
		'VALUES ("'. $_POST['computer_name'] .'",'. $_POST['x'] .','. $_POST['y'] .','. $regid .');');
} else {
	$dbTrackHandler->query(
		'UPDATE computers '.
		'SET '.
		'name = "'. $_POST['computer_name'] .'", '.
		'x = '. $_POST['x'] .', '.
		'y = '. $_POST['y'] .' '.
		'WHERE id='. $compid .';');
}

header('location: viewregion.php?id=' . $regid);
?>
