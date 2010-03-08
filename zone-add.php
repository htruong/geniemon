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

initSession();

if (!($_SESSION['loggedinUserPerms'] & EDIT_ZONES)) {
	die('You do not have permission to access this page!');
}

$dbTrackHandler = connectDb();

// Now process the image that has just been uploaded

$submittedImage = $_FILES['newZoneImg'];
$saveFolder = "maps/";
$regionName = $_POST['newZoneName'];

if($_SERVER["REQUEST_METHOD"] != "POST") {
	die('Invalid request.');
}

// From http://www.theopensurgery.com/29/php-upload-and-resize-image-script/
if (isset ($submittedImage)){
	$imagename = $submittedImage['name'];
	$source = $submittedImage['tmp_name'];
	$target = $saveFolder.$imagename;
	move_uploaded_file($source, $target);

	$imagepath = $imagename;
	$file = $saveFolder . $imagepath; //This is the original file

	list($mapWidth, $mapHeight) = getimagesize($file) ;
}

if (!$mapWidth) $mapWidth = '0';
if (!$mapHeight) $mapHeight = '0';


// Now add the new zone to the DB.
$zonesQuery = $dbTrackHandler->query(
	'INSERT INTO zones (region_name, region_width, region_height, region_map_img)' .
	" VALUES ('$regionName', $mapWidth, $mapHeight, '$target');"
	);

    die('INSERT INTO zones (region_name, region_width, region_height, region_map_img)' .
    " VALUES ('$regionName', $mapWidth, $mapHeight, '$target');");

// redirect to main page
header ('location: ./');

?>
